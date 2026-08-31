<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OutreachMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchImapReplies extends Command
{
    protected $signature = 'email:fetch-replies';
    protected $description = 'Fetches incoming email replies via IMAP ($0 setup) and synchronizes them into the 2-way inbox.';

    public function handle(): int
    {
        $host = env('IMAP_HOST', env('MAIL_HOST', 'mail.nexidant.com'));
        $port = env('IMAP_PORT', 993);
        $encryption = env('IMAP_ENCRYPTION', 'ssl');
        $username = env('IMAP_USERNAME', env('MAIL_USERNAME', 'info@nexidant.com'));
        $password = env('IMAP_PASSWORD', env('MAIL_PASSWORD', ''));

        $this->info("📥 Connecting to IMAP server ({$host}:{$port}/{$encryption}) for {$username}...");

        if (!function_exists('imap_open')) {
            $this->info("⚡ Running IMAP Sync via Python Engine (Native SSL imaplib)...");
            $runner = new \App\Services\EngineRunnerService();
            $result = $runner->runScript('fetch_imap_replies.py');

            if (!empty($result['output'])) {
                $this->line(trim($result['output']));
            }
            if (!empty($result['error_output'])) {
                $this->line(trim($result['error_output']));
            }

            return $result['success'] ? 0 : 1;
        }

        $mailboxFlags = "/imap/{$encryption}/novalidate-cert";
        $mailbox = "{" . "{$host}:{$port}{$mailboxFlags}" . "}INBOX";

        $connection = @imap_open($mailbox, $username, $password);

        if (!$connection) {
            $error = imap_last_error();
            $this->error("✕ Failed to connect to IMAP mailbox: {$error}");
            Log::warning("IMAP fetch failed: {$error}");
            return 1;
        }

        $this->info("✓ Connected to IMAP. Searching for recent messages...");

        // Search for recent messages in the last 7 days
        $sinceDate = date("d-M-Y", strtotime("-7 days"));
        $emails = imap_search($connection, "SINCE \"{$sinceDate}\"");

        if (!$emails) {
            $this->info("✅ No new incoming messages found in the last 7 days.");
            imap_close($connection);
            return 0;
        }

        $savedRepliesCount = 0;

        foreach ($emails as $msgNumber) {
            $header = imap_headerinfo($connection, $msgNumber);
            $fromAddress = $header->from[0]->mailbox . "@" . $header->from[0]->host;
            $fromName = isset($header->from[0]->personal) ? $header->from[0]->personal : $fromAddress;
            $subject = isset($header->subject) ? imap_utf8($header->subject) : 'No Subject';
            $messageId = isset($header->message_id) ? trim($header->message_id, '<>') : null;
            $inReplyTo = isset($header->in_reply_to) ? trim($header->in_reply_to, '<>') : null;
            $date = date("Y-m-d H:i:s", isset($header->udate) ? $header->udate : time());

            // Skip messages sent from our own mailbox
            if (strtolower($fromAddress) === strtolower($username)) {
                continue;
            }

            // Check if this inbound message is already recorded in the database
            $exists = OutreachMessage::where('message_id', $messageId)
                ->orWhere(function ($q) use ($fromAddress, $subject, $date) {
                    $q->where('sender_email', $fromAddress)
                      ->where('subject', $subject)
                      ->where('sent_at', $date);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            // Fetch email body
            $body = $this->getCleanBody($connection, $msgNumber);

            // Check if this is an automated bounce / failure notification
            $isBounce = str_contains(strtolower($fromAddress), 'mailer-daemon') 
                || str_contains(strtolower($fromAddress), 'postmaster')
                || str_contains(strtolower($subject), 'mail delivery failed')
                || str_contains(strtolower($subject), 'delivery status notification')
                || str_contains(strtolower($subject), 'undelivered mail');

            if ($isBounce) {
                // Extract failed recipient email from bounce body
                if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $body, $matches)) {
                    $failedEmail = $matches[0];
                    if (strtolower($failedEmail) !== strtolower($username)) {
                        OutreachMessage::where('recipient_email', $failedEmail)
                            ->where('direction', 'outbound')
                            ->latest()
                            ->first()
                            ?->update([
                                'status' => 'bounced',
                                'error_message' => substr($body, 0, 300),
                            ]);
                    }
                }
                $this->info("   ⚠️ Handled bounce notice from {$fromAddress} ({$subject})");
                continue;
            }

            // Find matching company/contact
            $contact = Contact::where('email', $fromAddress)->first();
            $company = null;

            if ($contact) {
                $company = $contact->company;
            } else {
                // Try finding by domain
                $senderDomain = substr(strrchr($fromAddress, "@"), 1);
                $company = Company::where('domain', $senderDomain)
                    ->orWhere('domain', 'like', '%' . $senderDomain . '%')
                    ->first();
            }

            // Find matching thread via in_reply_to
            $matchedOpportunityId = null;
            if ($inReplyTo) {
                $originalOutbound = OutreachMessage::where('message_id', $inReplyTo)->first();
                if ($originalOutbound) {
                    $matchedOpportunityId = $originalOutbound->opportunity_id;
                    if (!$company) $company = $originalOutbound->company;
                    if (!$contact) $contact = $originalOutbound->contact;
                }
            }

            // Record the incoming message in the 2-way inbox
            OutreachMessage::create([
                'company_id' => $company?->id,
                'contact_id' => $contact?->id,
                'opportunity_id' => $matchedOpportunityId,
                'sender_email' => $fromAddress,
                'recipient_email' => $username,
                'channel' => 'email',
                'direction' => 'inbound',
                'subject' => $subject,
                'body_text' => $body,
                'status' => 'delivered',
                'message_id' => $messageId,
                'in_reply_to' => $inReplyTo,
                'sent_at' => $date,
                'created_at' => now(),
            ]);

            // Mark company opportunities as 'in_discussion' or 'replied'
            if ($company) {
                Opportunity::where('company_id', $company->id)
                    ->where('status', '!=', 'converted')
                    ->update(['status' => 'in_discussion']);
            }

            $savedRepliesCount++;
            $this->info("   💬 Captured reply from {$fromAddress} ({$subject})");
        }

        imap_close($connection);
        $this->info("🎉 IMAP check complete. Synced {$savedRepliesCount} new incoming messages.");
        return 0;
    }

    private function getCleanBody($connection, $msgNumber): string
    {
        $body = imap_fetchbody($connection, $msgNumber, "1");
        if (empty($body)) {
            $body = imap_body($connection, $msgNumber);
        }

        // Handle quoted-printable or base64 decoding
        $structure = imap_fetchstructure($connection, $msgNumber);
        if (isset($structure->encoding)) {
            if ($structure->encoding == 3) {
                $body = base64_decode($body);
            } elseif ($structure->encoding == 4) {
                $body = quoted_printable_decode($body);
            }
        }

        return trim(strip_tags($body));
    }
}
