<?php

namespace App\Console\Commands;

use App\Models\OutreachMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RegenerateQueuedOutreach extends Command
{
    protected $signature = 'outreach:regenerate-drafts 
                            {--status=queued,failed : Comma-separated statuses to regenerate}
                            {--limit=500 : Maximum messages to process}';

    protected $description = 'Regenerates personalized cold outreach copy for all queued and failed messages using the clean AI copy engine.';

    public function handle(): int
    {
        $statuses = explode(',', $this->option('status'));
        $limit = (int) $this->option('limit');

        $this->info("⚡ Starting Clean Copy Re-generation for statuses: " . implode(', ', $statuses) . "...");

        $messages = OutreachMessage::with(['company', 'contact'])
            ->whereIn('status', $statuses)
            ->where('direction', 'outbound')
            ->where('step', 1)
            ->limit($limit)
            ->get();

        if ($messages->isEmpty()) {
            $this->info("✓ No messages found matching specified criteria.");
            return 0;
        }

        $this->info("Found {$messages->count()} message(s) to regenerate.");

        $enginePath = env('ENGINE_PATH', base_path('../signal-engine'));
        $python = env('PYTHON_BINARY', 'python3');
        $updatedCount = 0;
        $failedCount = 0;

        foreach ($messages as $msg) {
            $companyId = $msg->company_id;
            if (!$companyId) {
                continue;
            }

            $contactId = $msg->contact_id ?: 'null';
            $segment = $msg->segment ?: 'laravel_modernization';

            try {
                $cmd = escapeshellcmd("{$python} {$enginePath}/scripts/generate_single_copy.py {$companyId} {$contactId} {$segment}");
                $output = @shell_exec($cmd);

                if ($output) {
                    $res = json_decode(trim($output), true);
                    if ($res && isset($res['body_text']) && isset($res['subject'])) {
                        $msg->update([
                            'subject' => $res['subject'],
                            'body_text' => $res['body_text'],
                            'generator_type' => $res['generator_type'] ?? 'qwen3.5_0.8b',
                            'status' => 'queued',
                            'error_message' => null,
                            'staged_at' => now(),
                        ]);

                        $updatedCount++;
                        $this->line("   ✓ Regenerated #{$msg->id} for {$msg->recipient_email} [{$msg->subject}]");
                        continue;
                    }
                }

                // Fallback sanitize existing text in place if Python generation didn't return
                $cleanSubject = preg_replace('/^(?:\*\*|__)?\s*subject(?:\s*line)?\s*(?:\*\*|__)?\s*[:\-]\s*/i', '', $msg->subject);
                $cleanBody = preg_replace('/<think>.*?(?:<\/think>|$)/s', '', $msg->body_text);
                $cleanBody = preg_replace('/^(?:\*\*|__)?\s*subject(?:\s*line)?\s*(?:\*\*|__)?\s*[:\-].*?\n+/i', '', trim($cleanBody));

                $msg->update([
                    'subject' => trim($cleanSubject),
                    'body_text' => trim($cleanBody),
                    'status' => 'queued',
                    'error_message' => null,
                ]);

                $updatedCount++;
                $this->line("   ✓ Sanitized #{$msg->id} in place");

            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("   ✕ Failed to regenerate #{$msg->id}: " . $e->getMessage());
                Log::error("Failed to regenerate outreach message #{$msg->id}: " . $e->getMessage());
            }
        }

        $this->info("🎉 Done! Successfully regenerated and re-queued {$updatedCount} message(s). Failed: {$failedCount}.");
        return 0;
    }
}
