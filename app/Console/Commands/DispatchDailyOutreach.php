<?php

namespace App\Console\Commands;

use App\Models\Opportunity;
use App\Models\OutreachMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DispatchDailyOutreach extends Command
{
    protected $signature = 'outreach:dispatch-daily {--limit=250} {--dry-run}';
    protected $description = 'Dispatches the daily batch of personalized cold outreach emails with human-paced staggering and 1-per-company frequency capping.';

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?: env('DAILY_OUTREACH_LIMIT', 250));
        $isDryRun = $this->option('dry-run');

        $this->info("🚀 Starting Daily Outreach Dispatch (Daily Limit: {$limit}, Dry Run: " . ($isDryRun ? 'YES' : 'NO') . ")...");

        // 1. Check how many emails have already been sent today
        $sentTodayCount = OutreachMessage::whereDate('sent_at', today())
            ->where('direction', 'outbound')
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'replied'])
            ->count();

        $remainingAllowance = max(0, $limit - $sentTodayCount);

        if ($remainingAllowance <= 0) {
            $this->warn("⚠️ Daily limit of {$limit} emails already reached for today ({$sentTodayCount} sent). Halting dispatch.");
            return 0;
        }

        $this->info("📊 Daily progress: {$sentTodayCount} already sent. Remaining quota for today: {$remainingAllowance}.");

        // 2. Fetch all companies and emails that have already received an outbound cold pitch
        $alreadyPitchedCompanyIds = OutreachMessage::where('direction', 'outbound')
            ->where('step', 1)
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'replied'])
            ->whereNotNull('company_id')
            ->pluck('company_id')
            ->toArray();

        // 3. Query staged messages ready for dispatch (Prioritizing High Priority Leads with Score >= 75)
        $pendingMessages = OutreachMessage::with(['company', 'contact', 'opportunity'])
            ->join('companies', 'companies.id', '=', 'outreach_messages.company_id')
            ->leftJoinSub(
                'SELECT s1.* FROM scores s1 WHERE s1.id = (SELECT MAX(s2.id) FROM scores s2 WHERE s2.company_id = s1.company_id)',
                'latest_score',
                'latest_score.company_id',
                '=',
                'outreach_messages.company_id'
            )
            ->whereIn('outreach_messages.status', ['queued', 'staged'])
            ->where('outreach_messages.step', 1)
            ->where('outreach_messages.direction', 'outbound')
            ->where('outreach_messages.recipient_email', 'not like', '%.local')
            ->where('companies.domain', 'not like', '%.local')
            ->whereNotIn('outreach_messages.company_id', $alreadyPitchedCompanyIds)
            ->select('outreach_messages.*')
            ->orderByRaw("
                CASE 
                    WHEN latest_score.opportunity_score >= 75 THEN 1
                    WHEN latest_score.opportunity_score >= 60 THEN 2
                    ELSE 3
                END ASC,
                latest_score.opportunity_score DESC,
                outreach_messages.id ASC
            ")
            ->take($remainingAllowance)
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info("✅ No queued messages ready for dispatch right now. Run enrichment/staging to queue new leads.");
            return 0;
        }

        $this->info("📨 Found {$pendingMessages->count()} candidates for dispatch.");
        $dispatchedCount = 0;
        $failedCount = 0;
        $contactedCompanyIdsToday = [];

        foreach ($pendingMessages as $message) {
            // Double check company wasn't contacted in this loop
            if (in_array($message->company_id, $contactedCompanyIdsToday)) {
                continue;
            }

            $toEmail = $message->recipient_email ?: $message->contact?->email;
            if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                $message->update([
                    'status' => 'failed',
                    'error_message' => 'Invalid recipient email address.',
                ]);
                $failedCount++;
                continue;
            }

            $this->line("👉 Preparing dispatch for {$message->company?->name} ({$toEmail})...");

            $pdfPath = $message->company ? $message->company->report_pdf_path : null;
            $hasPdf = ($pdfPath && file_exists($pdfPath));
            $pdfBadge = $hasPdf ? " [📎 PDF Attached: " . basename($pdfPath) . "]" : " [No PDF]";

            if ($isDryRun) {
                $this->info("   [DRY RUN] Would send '{$message->subject}' to {$toEmail}{$pdfBadge}");
                $dispatchedCount++;
                $contactedCompanyIdsToday[] = $message->company_id;
                continue;
            }

            try {
                // Generate a unique RFC Message-ID for thread tracking
                $customMessageId = Str::uuid()->toString() . '@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'nexidant.com');
                $trackingUrl = url("/track/open/{$message->id}");
                $htmlBody = nl2br(e($message->body_text)) . "<br><br><img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

                Mail::html($htmlBody, function ($mail) use ($toEmail, $message, $customMessageId, $pdfPath) {
                    $mail->to($toEmail)
                         ->subject($message->subject)
                         ->getHeaders()
                         ->addIdHeader('Message-ID', $customMessageId);

                    if ($pdfPath && file_exists($pdfPath)) {
                        $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $message->company->name ?? 'Company');
                        $mail->attach($pdfPath, [
                            'as' => "{$companyName}_Website_Technical_Audit.pdf",
                            'mime' => 'application/pdf',
                        ]);
                    }
                });

                // Update message record
                $message->update([
                    'status' => 'delivered',
                    'sender_email' => config('mail.from.address', 'info@nexidant.com'),
                    'message_id' => $customMessageId,
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                // Update opportunity status to 'contacted'
                if ($message->company) {
                    Opportunity::where('company_id', $message->company_id)
                        ->where('status', '!=', 'converted')
                        ->update(['status' => 'contacted']);
                }

                // Schedule automated 3-day follow-up if not already scheduled
                $existingFollowup = OutreachMessage::where('company_id', $message->company_id)
                    ->where('contact_id', $message->contact_id)
                    ->where('step', 2)
                    ->exists();

                if (!$existingFollowup) {
                    OutreachMessage::create([
                        'step' => 2,
                        'company_id' => $message->company_id,
                        'contact_id' => $message->contact_id,
                        'opportunity_id' => $message->opportunity_id,
                        'recipient_email' => $toEmail,
                        'sender_email' => config('mail.from.address', 'info@nexidant.com'),
                        'channel' => 'email',
                        'direction' => 'outbound',
                        'segment' => $message->segment,
                        'generator_type' => $message->generator_type,
                        'subject' => "Re: {$message->subject}",
                        'body_text' => $this->generateFollowupBody($message),
                        'in_reply_to' => $customMessageId,
                        'status' => 'staged',
                        'scheduled_for' => now()->addDays(3)->setTime(10, rand(0, 50)),
                    ]);
                }

                $dispatchedCount++;
                $contactedCompanyIdsToday[] = $message->company_id;
                $this->info("   ✓ Sent successfully (ID: #{$message->id})" . ($hasPdf ? " [📎 Attached: " . basename($pdfPath) . "]" : ""));

            } catch (\Throwable $e) {
                $failedCount++;
                $message->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $this->error("   ✕ Failed to send: {$e->getMessage()}");
                Log::error("Outreach dispatch failed for message #{$message->id}: {$e->getMessage()}");
            }
        }

        $this->info("🎉 Batch finished. Dispatched: {$dispatchedCount}, Failed: {$failedCount}, Total Sent Today: " . ($sentTodayCount + $dispatchedCount));
        return 0;
    }

    private function generateFollowupBody(OutreachMessage $original): string
    {
        $contact = $original->contact;
        $firstName = $contact?->first_name ?: ($contact?->full_name ? explode(' ', $contact->full_name)[0] : 'there');
        $companyName = $original->company?->name ?: 'your company';
        $contactEmail = $original->recipient_email ?: $contact?->email;

        $senderName = env('SENDER_NAME', 'Supto Khan');
        $senderTitle = env('SENDER_TITLE', 'CEO');
        $companyBrand = env('COMPANY_NAME', 'Nexidant');
        $senderAddress = env('COMPANY_PHYSICAL_ADDRESS', 'H:3, R:3/A, Block - F, Sector - 15, Uttara, Dhaka, Bangladesh');

        return "Hi {$firstName},\n\n"
            . "Just following up on my previous note regarding {$companyName}'s web architecture and engineering bandwidth.\n\n"
            . "Did you have a chance to review the modernization ideas we drafted for your team?\n\n"
            . "Best,\n"
            . "{$senderName}\n"
            . "{$senderTitle}, {$companyBrand}\n"
            . "Full-Stack Engineering & Modernization\n\n"
            . "---\n"
            . "{$companyBrand} | {$senderAddress}\n"
            . "Unsubscribe: https://nexidant.com/unsubscribe?email={$contactEmail}";
    }
}
