<?php

namespace App\Console\Commands;

use App\Models\OutreachMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduledOutreach extends Command
{
    protected $signature = 'outreach:send-scheduled 
                            {--limit=25 : Maximum number of emails to dispatch in this batch run}
                            {--daily-limit=250 : Maximum total emails allowed to be sent per day}';
                            
    protected $description = 'Dispatches queued & scheduled cold outreach emails via configured SMTP with daily volume caps';

    public function handle(): int
    {
        @set_time_limit(0);
        $batchLimit = (int) $this->option('limit');
        $dailyLimit = (int) ($this->option('daily-limit') ?: env('DAILY_OUTREACH_LIMIT', 250));
        $now = now();
        $todayStart = $now->copy()->startOfDay();

        $this->info("⚡ [Outreach Scheduler] Checking scheduled & queued messages (Time: {$now->toDateTimeString()})...");

        // 1. Check how many emails have already been sent today (delivered or opened)
        $sentToday = OutreachMessage::whereNotNull('sent_at')
            ->where('sent_at', '>=', $todayStart)
            ->whereIn('status', ['delivered', 'sent', 'opened'])
            ->count();

        $this->info("📊 Daily Outreach Volume: {$sentToday} / {$dailyLimit} emails sent today.");

        if ($sentToday >= $dailyLimit) {
            $this->warn("🛑 [Daily Cap Reached] Already sent {$sentToday} emails today (Limit: {$dailyLimit}). Pausing sending until next peak day.");
            return 0;
        }

        // Calculate remaining quota for today
        $remainingToday = $dailyLimit - $sentToday;
        $effectiveLimit = min($batchLimit, $remainingToday);

        $this->info("Dispathing up to {$effectiveLimit} email(s) in this batch (Remaining today: {$remainingToday}).");

        // 2. Fetch pending queued messages
        $messages = OutreachMessage::with(['company', 'contact'])
            ->whereIn('status', ['queued', 'staged'])
            ->where('recipient_email', 'not like', '%.local')
            ->where(function ($q) use ($now) {
                $q->whereNull('scheduled_for')
                  ->orWhere('scheduled_for', '<=', $now);
            })
            ->orderBy('id', 'asc')
            ->limit($effectiveLimit)
            ->get();

        if ($messages->isEmpty()) {
            $this->info("✓ No pending queued messages ready to send right now.");
            return 0;
        }

        $this->info("Found {$messages->count()} message(s) to dispatch.");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($messages as $msg) {
            @set_time_limit(60);
            // Double check we haven't hit daily limit mid-loop
            if (($sentToday + $sentCount) >= $dailyLimit) {
                $this->warn("🛑 Hit daily limit of {$dailyLimit} emails during execution. Stopping batch.");
                break;
            }

            $toEmail = $msg->recipient_email ?: $msg->contact?->email;
            if (!$toEmail) {
                $this->warn("⚠️ Skipping message #{$msg->id}: No recipient email found.");
                $msg->update([
                    'status' => 'failed',
                    'error_message' => 'No recipient email specified.'
                ]);
                $failedCount++;
                continue;
            }

            try {
                $this->line("📤 Sending to {$toEmail} (Company: {$msg->company?->name})...");

                // Append 1x1 invisible open tracking pixel
                $trackingUrl = url("/track/open/{$msg->id}");
                $htmlBody = nl2br(e($msg->body_text)) . "<br><br><img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

                $pdfPath = $msg->company ? $msg->company->report_pdf_path : null;

                Mail::html($htmlBody, function ($mail) use ($toEmail, $msg, $pdfPath) {
                    $mail->to($toEmail)
                         ->subject($msg->subject);

                    if ($pdfPath && file_exists($pdfPath)) {
                        $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $msg->company->name ?? 'Company');
                        $mail->attach($pdfPath, [
                            'as' => "{$companyName}_Website_Technical_Audit.pdf",
                            'mime' => 'application/pdf',
                        ]);
                    }
                });

                $msg->update([
                    'status' => 'delivered',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                // Update company opportunities status
                if ($msg->company) {
                    foreach ($msg->company->opportunities as $opp) {
                        $opp->update(['status' => 'contacted']);
                    }
                }

                $hasPdf = ($pdfPath && file_exists($pdfPath));
                $this->info("✓ Delivered #{$msg->id} -> {$toEmail}" . ($hasPdf ? " [📎 Attached: " . basename($pdfPath) . "]" : ""));
                $sentCount++;

                // Throttling delay between sends to protect SMTP reputation (2-3 seconds)
                usleep(2500000);
            } catch (\Exception $e) {
                $this->error("❌ Failed to send #{$msg->id} to {$toEmail}: " . $e->getMessage());
                $msg->update([
                    'status' => 'failed',
                    'error_message' => substr($e->getMessage(), 0, 500),
                ]);
                $failedCount++;
            }
        }

        $totalToday = $sentToday + $sentCount;
        $this->info("🎉 Finished execution: {$sentCount} sent in this run. Total for today: {$totalToday} / {$dailyLimit}.");
        return 0;
    }
}
