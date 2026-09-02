<?php

namespace App\Console\Commands;

use App\Models\OutreachMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessScheduledFollowups extends Command
{
    protected $signature = 'outreach:process-followups {--limit=50} {--dry-run}';
    protected $description = 'Dispatches scheduled 3-day follow-up outreach emails in the same thread, checking for prior replies (Capped at 50/day).';

    public function handle(): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $limit = (int) ($this->option('limit') ?: env('FOLLOWUP_DAILY_LIMIT', 50));
        $isDryRun = $this->option('dry-run');

        $this->info("🔍 Checking for due 3-day follow-up messages (Daily Limit: {$limit}, Dry Run: " . ($isDryRun ? 'YES' : 'NO') . ")...");

        // 1. Check how many follow-up emails have already been sent today
        $sentTodayCount = OutreachMessage::whereDate('sent_at', today())
            ->where('step', 2)
            ->where('direction', 'outbound')
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'replied'])
            ->count();

        $remainingAllowance = max(0, $limit - $sentTodayCount);

        if ($remainingAllowance <= 0) {
            $this->warn("⚠️ Daily follow-up limit of {$limit} already reached for today ({$sentTodayCount} sent). Halting dispatch.");
            return 0;
        }

        $this->info("📊 Follow-up progress: {$sentTodayCount} sent today. Remaining quota: {$remainingAllowance}.");

        // 2. Query oldest due follow-ups capped at remaining allowance
        $dueFollowups = OutreachMessage::with(['company', 'contact'])
            ->where('step', 2)
            ->where('direction', 'outbound')
            ->where('status', 'staged')
            ->where('scheduled_for', '<=', now())
            ->orderBy('scheduled_for', 'asc')
            ->orderBy('id', 'asc')
            ->take($remainingAllowance)
            ->get();

        if ($dueFollowups->isEmpty()) {
            $this->info("✅ No follow-up emails due for dispatch right now.");
            return 0;
        }

        $this->info("📨 Found {$dueFollowups->count()} due follow-ups queued for dispatch.");
        $dispatched = 0;
        $cancelled = 0;

        foreach ($dueFollowups as $followup) {
            @set_time_limit(60);
            $toEmail = $followup->recipient_email ?: $followup->contact?->email;

            // Check if there was an inbound reply from this contact or company
            $hasReplied = OutreachMessage::where(function ($q) use ($followup, $toEmail) {
                    $q->where('company_id', $followup->company_id)
                      ->orWhere('recipient_email', $toEmail)
                      ->orWhere('sender_email', $toEmail);
                })
                ->where('direction', 'inbound')
                ->exists();

            if ($hasReplied) {
                $followup->update([
                    'status' => 'cancelled',
                    'error_message' => 'Prospect already replied. Follow-up cancelled.',
                ]);
                $this->warn("   ✕ Cancelled follow-up for {$toEmail} (prospect already replied).");
                $cancelled++;
                continue;
            }

            if ($isDryRun) {
                $this->info("   [DRY RUN] Would send Step 2 follow-up '{$followup->subject}' to {$toEmail}");
                $dispatched++;
                continue;
            }

            try {
                $customMessageId = \Illuminate\Support\Str::uuid()->toString() . '@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'nexidant.com');
                $trackingUrl = url("/track/open/{$followup->id}");
                $htmlBody = nl2br(e($followup->body_text)) . "<br><br><img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

                $pdfPath = $followup->company ? $followup->company->report_pdf_path : null;

                Mail::html($htmlBody, function ($mail) use ($toEmail, $followup, $customMessageId, $pdfPath) {
                    $mail->to($toEmail)
                         ->subject($followup->subject)
                         ->getHeaders()
                         ->addIdHeader('Message-ID', $customMessageId);

                    if ($pdfPath && file_exists($pdfPath)) {
                        $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $followup->company->name ?? 'Company');
                        $mail->attach($pdfPath, [
                            'as' => "{$companyName}_Website_Technical_Audit.pdf",
                            'mime' => 'application/pdf',
                        ]);
                    }

                    if ($followup->in_reply_to) {
                        $cleanInReplyTo = trim($followup->in_reply_to, '<>');
                        $mail->getHeaders()
                             ->addIdHeader('In-Reply-To', $cleanInReplyTo)
                             ->addIdHeader('References', $cleanInReplyTo);
                    }
                });

                $followup->update([
                    'status' => 'delivered',
                    'message_id' => $customMessageId,
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                $dispatched++;
                $this->info("   ✓ Dispatched Step 2 follow-up to {$toEmail}");

                // Human-paced anti-burst staggering to protect Brevo SMTP reputation
                usleep(1500000);

            } catch (\Throwable $e) {
                $followup->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $this->error("   ✕ Failed to send follow-up to {$toEmail}: {$e->getMessage()}");
                Log::error("Follow-up dispatch failed for message #{$followup->id}: {$e->getMessage()}");
            }
        }

        $this->info("🎉 Follow-up sweep complete. Dispatched: {$dispatched}, Cancelled: {$cancelled}");
        return 0;
    }
}
