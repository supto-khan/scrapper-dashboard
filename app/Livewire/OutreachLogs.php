<?php

namespace App\Livewire;

use App\Models\OutreachMessage;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class OutreachLogs extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';
    public string $search = '';
    public int $perPage = 15;

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sendQueuedMessage(int $messageId): void
    {
        $msg = OutreachMessage::with(['company', 'contact'])->find($messageId);
        if (!$msg) return;

        try {
            $toEmail = $msg->recipient_email ?: $msg->contact?->email;
            if (!$toEmail) {
                throw new \Exception("No recipient email found for this message.");
            }

            // Append 1x1 tracking pixel to HTML email
            $trackingUrl = url("/track/open/{$msg->id}");
            $htmlBody = nl2br(e($msg->body_text)) . "<br><br><img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

            Mail::html($htmlBody, function ($message) use ($toEmail, $msg) {
                $message->to($toEmail)
                        ->subject($msg->subject);
            });

            $msg->update([
                'status' => 'delivered',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            session()->flash('success_message', "Message #{$msg->id} delivered successfully to {$toEmail}!");
        } catch (\Exception $e) {
            $msg->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            session()->flash('error_message', "Delivery failed for #{$msg->id}: " . $e->getMessage());
        }
    }

    public function retryAllFailed(): void
    {
        $count = OutreachMessage::where('status', 'failed')->update([
            'status' => 'queued',
            'error_message' => null,
        ]);

        session()->flash('success_message', "Re-queued {$count} failed message(s) for automated dispatch!");
    }

    public function regenerateAllDrafts(): void
    {
        try {
            $basePath = base_path();
            $phpBinary = PHP_BINARY;
            $command = "{$phpBinary} {$basePath}/artisan outreach:regenerate-drafts --status=queued,failed --limit=500 > /dev/null 2>&1 &";
            exec($command);

            session()->flash('success_message', '⚡ AI copy re-generation started in the background! All queued/failed drafts are being cleanly re-synthesized.');
        } catch (\Throwable $e) {
            session()->flash('error_message', "Re-generation failed: " . $e->getMessage());
        }
    }

    public function dispatchAllQueued(): void
    {
        try {
            $basePath = base_path();
            $phpBinary = PHP_BINARY;
            $command = "{$phpBinary} {$basePath}/artisan outreach:send-scheduled --limit=500 --daily-limit=1000 > /dev/null 2>&1 &";
            exec($command);

            session()->flash('success_message', '🚀 Background email dispatch initiated! Emails are being sent sequentially with anti-spam throttling.');
        } catch (\Exception $e) {
            session()->flash('error_message', "Dispatch failed: " . $e->getMessage());
        }
    }

    public function render()
    {
        $stats = [
            'total' => OutreachMessage::count(),
            'delivered' => OutreachMessage::whereIn('status', ['delivered', 'sent', 'opened'])->count(),
            'opened' => OutreachMessage::where('open_count', '>', 0)->count(),
            'failed' => OutreachMessage::where('status', 'failed')->count(),
            'queued' => OutreachMessage::where('status', 'queued')->count(),
        ];

        $query = OutreachMessage::with(['company', 'contact'])->latest();

        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'opened') {
                $query->where('open_count', '>', 0);
            } elseif ($this->statusFilter === 'queued') {
                $query->whereIn('status', ['queued', 'staged']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('recipient_email', 'like', "%{$this->search}%")
                  ->orWhere('subject', 'like', "%{$this->search}%")
                  ->orWhereHas('company', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('domain', 'like', "%{$this->search}%");
                  });
            });
        }

        $messages = $query->paginate($this->perPage);

        return view('livewire.outreach-logs', [
            'stats' => $stats,
            'messages' => $messages,
        ])->layout('components.layouts.app');
    }
}
