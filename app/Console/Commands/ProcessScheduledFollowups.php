<?php

namespace App\Console\Commands;

use App\Models\OutreachMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessScheduledFollowups extends Command
{
    protected $signature = 'outreach:process-followups {--dry-run}';
    protected $description = 'Dispatches scheduled 3-day follow-up outreach emails in the same thread, checking for prior replies.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info("🔍 Checking for due 3-day follow-up messages...");

        $dueFollowups = OutreachMessage::with(['company', 'contact'])
            ->where('step', 2)
            ->where('direction', 'outbound')
            ->where('status', 'staged')
            ->where('scheduled_for', '<=', now())
            ->orderBy('id', 'asc')
            ->get();

        if ($dueFollowups->isEmpty()) {
            $this->info("✅ No follow-up emails due for dispatch right now.");
            return 0;
        }

        $this->info("📨 Found {$dueFollowups->count()} due follow-ups.");
        $dispatched = 0;
        $cancelled = 0;

        foreach ($dueFollowups as $followup) {
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
                $trackingUrl = url("/track/open/{$followup->id}");
                $htmlBody = nl2br(e($followup->body_text)) . "<br><br><img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

                Mail::html($htmlBody, function ($mail) use ($toEmail, $followup) {
                    $mail->to($toEmail)
                         ->subject($followup->subject);

                    if ($followup->in_reply_to) {
                        $cleanInReplyTo = trim($followup->in_reply_to, '<>');
                        $mail->getHeaders()
                             ->addIdHeader('In-Reply-To', $cleanInReplyTo)
                             ->addIdHeader('References', $cleanInReplyTo);
                    }
                });

                $followup->update([
                    'status' => 'delivered',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                $dispatched++;
                $this->info("   ✓ Dispatched Step 2 follow-up to {$toEmail}");

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
