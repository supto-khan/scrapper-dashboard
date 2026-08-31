<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OutreachMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class InboxDashboard extends Component
{
    use WithPagination;

    public string $activeFolder = 'inbox'; // 'inbox', 'sent', 'followups', 'all'
    public string $search = '';
    public ?int $selectedThreadId = null;

    // Quick Reply Composer state
    public string $replySubject = '';
    public string $replyBody = '';
    public string $replyRecipient = '';

    protected $queryString = [
        'activeFolder' => ['except' => 'inbox'],
        'search' => ['except' => ''],
    ];

    public function setFolder(string $folder): void
    {
        $this->activeFolder = $folder;
        $this->selectedThreadId = null;
        $this->resetPage();
    }

    public function selectThread(int $messageId): void
    {
        $this->selectedThreadId = $messageId;
        $message = OutreachMessage::find($messageId);
        if ($message) {
            $this->replyRecipient = $message->direction === 'inbound' 
                ? $message->sender_email 
                : ($message->recipient_email ?: $message->contact?->email ?? '');
            $this->replySubject = str_starts_with($message->subject, 'Re:') ? $message->subject : "Re: {$message->subject}";
            $this->replyBody = '';
            
            // Mark as read
            if ($message->direction === 'inbound' && !$message->read_at) {
                $message->update(['read_at' => now()]);
            }
        }
    }

    public function sendQuickReply(): void
    {
        if (!$this->selectedThreadId || empty(trim($this->replyBody))) {
            return;
        }

        $activeMsg = OutreachMessage::with(['company', 'contact'])->findOrFail($this->selectedThreadId);
        $toEmail = $this->replyRecipient ?: ($activeMsg->direction === 'inbound' ? $activeMsg->sender_email : $activeMsg->recipient_email);

        if (!$toEmail) {
            session()->flash('inbox_error', 'No valid recipient email address.');
            return;
        }

        try {
            $senderEmail = config('mail.from.address', 'info@nexidant.com');
            $customMessageId = Str::uuid()->toString() . '@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'nexidant.com');

            $senderName = env('SENDER_NAME', 'Supto Khan');
            $senderTitle = env('SENDER_TITLE', 'CEO');
            $companyBrand = env('COMPANY_NAME', 'Nexidant');
            $senderAddress = env('COMPANY_PHYSICAL_ADDRESS', 'H:3, R:3/A, Block - F, Sector - 15, Uttara, Dhaka, Bangladesh');

            $fullBody = trim($this->replyBody) . "\n\n"
                . "Best,\n"
                . "{$senderName}\n"
                . "{$senderTitle}, {$companyBrand}\n"
                . "Full-Stack Engineering & Modernization\n\n"
                . "---\n"
                . "{$companyBrand} | {$senderAddress}\n"
                . "Unsubscribe: https://nexidant.com/unsubscribe?email={$toEmail}";

            $htmlBody = nl2br(e($fullBody));

            Mail::html($htmlBody, function ($mail) use ($toEmail, $customMessageId, $activeMsg) {
                $mail->to($toEmail)
                     ->subject($this->replySubject)
                     ->getHeaders()
                     ->addIdHeader('Message-ID', $customMessageId);

                if ($activeMsg->message_id) {
                    $cleanInReplyTo = trim($activeMsg->message_id, '<>');
                    $mail->getHeaders()
                         ->addIdHeader('In-Reply-To', $cleanInReplyTo)
                         ->addIdHeader('References', $cleanInReplyTo);
                }
            });

            // Store the outbound reply in database
            OutreachMessage::create([
                'company_id' => $activeMsg->company_id,
                'contact_id' => $activeMsg->contact_id,
                'opportunity_id' => $activeMsg->opportunity_id,
                'sender_email' => $senderEmail,
                'recipient_email' => $toEmail,
                'channel' => 'email',
                'direction' => 'outbound',
                'subject' => $this->replySubject,
                'body_text' => $fullBody,
                'status' => 'delivered',
                'message_id' => $customMessageId,
                'in_reply_to' => $activeMsg->message_id,
                'sent_at' => now(),
                'created_at' => now(),
            ]);

            // Update opportunity status to 'in_discussion'
            if ($activeMsg->company_id) {
                Opportunity::where('company_id', $activeMsg->company_id)
                    ->where('status', '!=', 'converted')
                    ->update(['status' => 'in_discussion']);
            }

            $this->replyBody = '';
            session()->flash('inbox_success', "✓ Reply sent directly to {$toEmail}!");
        } catch (\Throwable $e) {
            session()->flash('inbox_error', "Failed to send reply: " . $e->getMessage());
        }
    }

    public function render()
    {
        $query = OutreachMessage::with(['company', 'contact', 'opportunity']);

        if ($this->activeFolder === 'inbox') {
            $query->where('direction', 'inbound')
                  ->where('sender_email', 'not like', '%mailer-daemon%')
                  ->where('sender_email', 'not like', '%postmaster%')
                  ->where('subject', 'not like', '%Mail delivery failed%')
                  ->where('subject', 'not like', '%Delivery Status Notification%')
                  ->where('subject', 'not like', '%Undelivered Mail%');
        } elseif ($this->activeFolder === 'sent') {
            $query->where('direction', 'outbound')->whereIn('status', ['sent', 'delivered', 'opened', 'clicked']);
        } elseif ($this->activeFolder === 'followups') {
            $query->where('step', 2)->where('status', 'staged');
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('recipient_email', 'like', '%' . $this->search . '%')
                  ->orWhere('sender_email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('company', function ($cq) {
                      $cq->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('domain', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $threads = $query->orderBy('id', 'desc')->paginate(20);

        // Calculate counts for badges (excluding automated bounce notices from inbox count)
        $inboxCount = OutreachMessage::where('direction', 'inbound')
            ->where('sender_email', 'not like', '%mailer-daemon%')
            ->where('sender_email', 'not like', '%postmaster%')
            ->where('subject', 'not like', '%Mail delivery failed%')
            ->where('subject', 'not like', '%Delivery Status Notification%')
            ->where('subject', 'not like', '%Undelivered Mail%')
            ->count();
        $sentCount = OutreachMessage::where('direction', 'outbound')->whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])->count();
        $followupsCount = OutreachMessage::where('step', 2)->where('status', 'staged')->count();

        // Selected conversation history
        $selectedMessage = $this->selectedThreadId ? OutreachMessage::with(['company', 'contact'])->find($this->selectedThreadId) : null;
        $conversationHistory = collect();

        if ($selectedMessage) {
            $otherPartyEmail = $selectedMessage->direction === 'inbound'
                ? $selectedMessage->sender_email
                : ($selectedMessage->recipient_email ?: $selectedMessage->contact?->email);

            if ($selectedMessage->company_id) {
                $conversationHistory = OutreachMessage::where('company_id', $selectedMessage->company_id)
                    ->orderBy('id', 'asc')
                    ->get();
            } elseif ($otherPartyEmail && !str_contains($otherPartyEmail, 'nexidant.com')) {
                $conversationHistory = OutreachMessage::where(function ($q) use ($otherPartyEmail) {
                    $q->where('recipient_email', $otherPartyEmail)
                      ->orWhere('sender_email', $otherPartyEmail);
                })->orderBy('id', 'asc')->get();
            } else {
                $conversationHistory = collect([$selectedMessage]);
            }
        }

        return view('livewire.inbox-dashboard', [
            'threads' => $threads,
            'inboxCount' => $inboxCount,
            'sentCount' => $sentCount,
            'followupsCount' => $followupsCount,
            'selectedMessage' => $selectedMessage,
            'conversationHistory' => $conversationHistory,
        ])->layout('components.layouts.app');
    }
}
