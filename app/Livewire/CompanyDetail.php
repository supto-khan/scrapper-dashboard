<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OutreachMessage;
use Livewire\Component;

class CompanyDetail extends Component
{
    public int $companyId;
    public Company $company;

    // Email Studio State
    public ?int $selectedContactId = null;
    public ?int $selectedOpportunityId = null;
    public string $selectedSegment = 'laravel_modernization';
    public string $emailSubject = '';
    public string $emailBody = '';
    public bool $isQueued = false;
    public string $copyStatusMessage = '';

    public function mount(int $id): void
    {
        $this->companyId = $id;
        $this->loadCompany();
        $this->initializeEmailDraft();
    }

    public function loadCompany(): void
    {
        $this->company = Company::with([
            'latestScore',
            'latestTechnology',
            'latestAudit',
            'opportunities',
            'signals',
            'contacts',
            'outreachMessages',
        ])->findOrFail($this->companyId);

        // Select first contact by default if available
        if (!$this->selectedContactId && $this->company->contacts->isNotEmpty()) {
            $this->selectedContactId = $this->company->contacts->first()->id;
        }

        // Select first opportunity by default if not set
        if (!$this->selectedOpportunityId && $this->company->opportunities->isNotEmpty()) {
            $this->selectedOpportunityId = $this->company->opportunities->first()->id;
            $opp = $this->company->opportunities->first();
            if ($opp) {
                if ($opp->type === 'wordpress_rebuild' || $opp->type === 'cms_to_laravel_migration') {
                    $this->selectedSegment = 'laravel_modernization';
                } elseif ($opp->type === 'frontend_modernization') {
                    $this->selectedSegment = 'frontend_rebuild';
                } elseif ($opp->type === 'staff_augmentation') {
                    $this->selectedSegment = 'hiring_overflow';
                } elseif ($opp->type === 'performance_optimization') {
                    $this->selectedSegment = 'performance_revamp';
                }
            }
        }
    }

    public function selectOpportunity(int $oppId): void
    {
        $this->selectedOpportunityId = $oppId;
        $opp = $this->company->opportunities->firstWhere('id', $oppId);

        if ($opp) {
            if ($opp->type === 'wordpress_rebuild' || $opp->type === 'cms_to_laravel_migration') {
                $this->selectedSegment = 'laravel_modernization';
            } elseif ($opp->type === 'frontend_modernization') {
                $this->selectedSegment = 'frontend_rebuild';
            } elseif ($opp->type === 'staff_augmentation') {
                $this->selectedSegment = 'hiring_overflow';
            } elseif ($opp->type === 'performance_optimization') {
                $this->selectedSegment = 'performance_revamp';
            }
        }

        // Regenerate and update draft when user explicitly changes opportunity target
        $this->generateEmailCopy();
        if ($this->isQueued) {
            $this->queueOutreachMessage();
        }
    }

    public function getLlmPromptProperty(): string
    {
        $contact = $this->company->contacts->firstWhere('id', $this->selectedContactId);
        $contactName = $contact ? ($contact->full_name . ' (' . ($contact->title ?: 'Decision Maker') . ')') : 'Founder / CTO';
        $contactEmail = $contact ? $contact->email : 'Not enriched';

        $tech = $this->company->latestTechnology;
        $audit = $this->company->latestAudit;
        $score = $this->company->latestScore;

        $cms = $tech?->cms ?: 'Custom Backend';
        $frontend = !empty($tech?->frontend_stack) ? implode(', ', $tech->frontend_stack) : 'Standard / Legacy JavaScript';
        $security = 'HTTPS: ' . ($tech?->https ? 'Yes' : 'No') . ', HSTS: ' . ($tech?->hsts ? 'Yes' : 'No');
        $perf = $audit?->performance_score ? "{$audit->performance_score}/100" : 'Not audited';
        $lcp = $audit?->lcp_ms ? "{$audit->lcp_ms}ms" : 'N/A';

        $selectedOpp = $this->company->opportunities->firstWhere('id', $this->selectedOpportunityId)
            ?? $this->company->opportunities->first();

        $oppsList = [];
        foreach ($this->company->opportunities as $opp) {
            $isSelected = ($opp->id === $selectedOpp?->id);
            $prefix = $isSelected ? "⭐ [PRIMARY FOCUS FOR EMAIL #1]" : "- [FOLLOW-UP SIGNAL]";
            $valRange = '$' . number_format($opp->estimated_value_low / 1000) . 'k - $' . number_format($opp->estimated_value_high / 1000) . 'k';
            $evidenceStr = !empty($opp->evidence) ? json_encode($opp->evidence) : 'Detected from tech scan';
            $oppsList[] = "{$prefix} Service: {$opp->recommended_service} (Est. Value: {$valRange}, Confidence: " . round($opp->confidence * 100) . "%)\n  Evidence / Pain: {$evidenceStr}";
        }
        $oppsText = !empty($oppsList) ? implode("\n\n", $oppsList) : "- General Tech Modernization & Software Augmentation";

        $focusServiceName = $selectedOpp ? $selectedOpp->recommended_service : "Technical Architecture Modernization";

        return <<<PROMPT
You are an expert B2B SaaS & Tech Sales Copywriter for Nexidiant Software. Write a high-converting, personalized cold outreach email focused strictly on the SELECTED PRIMARY PAIN POINT:

### SELECTED TARGET OPPORTUNITY (FOCUS FOR EMAIL #1):
👉 {$focusServiceName}

### PROSPECT DOSSIER
- Company Name: {$this->company->name}
- Domain / Website: https://{$this->company->domain}
- Industry / Niche: {$this->company->industry}
- Team Size: {$this->company->employee_count_estimate}
- Lead Priority Score: {$score?->opportunity_score}/100 ({$score?->priority_tier})
- Target Contact: {$contactName}
- Target Email: {$contactEmail}

### TECHNICAL FINGERPRINT & DIAGNOSTICS
- CMS / Architecture: {$cms}
- Frontend Libraries: {$frontend}
- Security & SSL: {$security}
- Mobile Performance Score: {$perf}
- Core Web Vitals LCP: {$lcp}

### DETECTED SALES OPPORTUNITIES & PAIN POINTS
{$oppsText}

### SENDER PROFILE
- Agency: Nexidant
- Sender Name: Supto Khan (CEO)
- Core Strengths: Full-stack Laravel & React rebuilds, dedicated engineering team augmentation (squad scaling), Core Web Vitals & conversion optimization.

### STRICT COPYWRITING RULES:
1. ONLY 1 Problem Per Email: Write Email #1 focused EXCLUSIVELY on the PRIMARY FOCUS: "{$focusServiceName}". (Do not mention the other signals in Email #1).
2. Word Count: Keep the entire email body UNDER 100 words (concise, founder-to-founder tone, no corporate fluff).
3. Value Metric: Include 1 specific, measurable upside metric relevant to their industry (e.g. "shaving 1.5s off page load typically lifts checkout conversions by 15-20%" or "modernizing UI components speeds up feature release cycles by 2x").
4. Low-Friction CTA: End with a single, casual, zero-obligation question (e.g. "Open to checking out the 3 quick fixes we drafted for your team?").
5. Follow-Up Hooks: Provide 2 short follow-up ideas (1 sentence each) utilizing the secondary follow-up signals if they do not reply to Email #1.

### OUTPUT FORMAT:
- 3 Subject Line Options (under 45 characters)
- Email #1 (Under 100 words, focused on {$focusServiceName})
- Follow-Up #1 Angle (Day 4)
- Follow-Up #2 Angle (Day 8)
- CAN-SPAM Compliant Signature Block
PROMPT;
    }

    public function updatedSelectedContactId(): void
    {
        $this->loadDraftForCurrentSelection();
    }

    public function updatedSelectedSegment(): void
    {
        $this->generateEmailCopy();
        if ($this->isQueued) {
            $this->queueOutreachMessage();
        }
    }

    public function initializeEmailDraft(): void
    {
        $this->loadDraftForCurrentSelection();
    }

    public function loadDraftForCurrentSelection(): void
    {
        // 1. Check if there is an existing outreach message for this contact or company
        $existing = null;
        if ($this->selectedContactId) {
            $existing = $this->company->outreachMessages->firstWhere('contact_id', $this->selectedContactId);
        }
        if (!$existing) {
            $existing = $this->company->outreachMessages->first();
        }

        if ($existing) {
            $this->emailSubject = $existing->subject;
            $this->emailBody = $existing->body_text;
            if ($existing->segment) {
                $this->selectedSegment = $existing->segment;
            }
            if ($existing->contact_id) {
                $this->selectedContactId = $existing->contact_id;
            }
            $this->isQueued = in_array($existing->status, ['queued', 'delivered', 'sent', 'opened', 'failed', 'staged']);
            $this->copyStatusMessage = "Loaded saved message (Status: {$existing->status}).";
        } else {
            $this->generateEmailCopy();
        }
    }

    public function generateEmailCopy(): void
    {
        $contact = $this->company->contacts->firstWhere('id', $this->selectedContactId);
        $contactIdArg = $contact ? $contact->id : 'null';
        $contactEmail = $contact ? $contact->email : 'team@' . $this->company->domain;
        $enginePath = env('ENGINE_PATH', base_path('../signal-engine'));
        $python = env('PYTHON_BINARY', 'python3');

        // 1. Try running Python Qwen3.5-0.8B copy engine
        try {
            $cmd = escapeshellcmd("{$python} {$enginePath}/scripts/generate_single_copy.py {$this->company->id} {$contactIdArg} {$this->selectedSegment}");
            $output = @shell_exec($cmd);

            if ($output) {
                $res = json_decode(trim($output), true);
                if ($res && isset($res['body_text']) && isset($res['subject'])) {
                    $this->emailSubject = $res['subject'];
                    $this->emailBody = $res['body_text'];
                    $genType = $res['generator_type'] ?? 'ai_engine';
                    $this->copyStatusMessage = "Draft synthesized via {$genType}.";
                    return;
                }
            }
        } catch (\Throwable $e) {
            // Proceed to fallback
        }

        // 2. Fallback to PHP Template Engine
        $rawCompany = $this->company->name ?: $this->company->domain;
        $cleanCompany = preg_replace('/\.(com|org|net|io|ai|co|agency|digital|tech|us|uk|de|ca|dev)$/i', '', $rawCompany);
        $cleanCompany = ucwords(str_replace(['-', '_'], ' ', $cleanCompany));
        $companyName = $cleanCompany;

        $rawContactName = $contact ? ($contact->first_name ?: explode(' ', $contact->full_name)[0]) : null;
        $isBotName = false;
        if ($rawContactName) {
            $lower = strtolower(trim($rawContactName));
            if (str_contains($lower, '.') || str_contains($lower, '@') || in_array($lower, ['team', 'info', 'contact', 'support', 'admin', 'sales', 'hello', 'hi', 'careers', 'office', 'decision', 'maker'])) {
                $isBotName = true;
            }
        }

        if (!$rawContactName || $isBotName) {
            $contactName = $companyName ? "{$companyName} team" : "there";
        } else {
            $contactName = $rawContactName;
        }

        $signature = "Best,\n"
            . "Supto Khan\n"
            . "CEO, Nexidant\n"
            . "Full-Stack Engineering & Modernization\n\n"
            . "---\n"
            . "Nexidant | H:3, R:3/A, Block - F, Sector - 15, Uttara, Dhaka, Bangladesh\n"
            . "Unsubscribe: https://nexidant.com/unsubscribe?email={$contactEmail}";

        if ($this->selectedSegment === 'frontend_rebuild') {
            $this->emailSubject = "Quick question about {$this->company->domain}'s frontend architecture";
            $this->emailBody = "Hi {$contactName},\n\n"
                . "I was looking into {$companyName} and noticed your web presence is currently built with {$frontendStr}.\n\n"
                . "We've been helping high-growth teams modernize their client-facing apps into snappy, reactive React and Angular interfaces with zero downtime.\n\n"
                . "Would you be open to a 10-minute chat next week to see how we could help {$companyName} speed up UI release cycles?\n\n"
                . $signature;
        } elseif ($this->selectedSegment === 'hiring_overflow') {
            $this->emailSubject = "Dedicated full-stack engineering support for {$companyName}";
            $this->emailBody = "Hi {$contactName},\n\n"
                . "I noticed {$companyName} has been scaling up and looking for skilled engineering talent.\n\n"
                . "At Nexidant, we provide dedicated, pre-vetted full-stack engineering teams (Laravel, Vue, React, Python) that plug directly into your sprint cycle within 48 hours without the overhead of lengthy recruiting.\n\n"
                . "Are you open to exploring dedicated bandwidth for your upcoming roadmap?\n\n"
                . $signature;
        } elseif ($this->selectedSegment === 'performance_revamp') {
            $lcpStr = $audit?->lcp_ms ? "Largest Contentful Paint at {$audit->lcp_ms}ms" : "Core Web Vitals latency";
            $this->emailSubject = "Audit notes on {$this->company->domain}'s Core Web Vitals";
            $this->emailBody = "Hi {$contactName},\n\n"
                . "Ran a fast technical diagnostic on {$this->company->domain} and noticed several optimization opportunities, particularly around {$lcpStr} and asset payloads.\n\n"
                . "For sites in {$this->company->industry}, trimming 1.5s off page load typically drives an immediate 15-25% boost in customer conversion rates.\n\n"
                . "I put together 3 specific fixes for your team. Would you like me to send them over?\n\n"
                . $signature;
        } else {
            // Default: Laravel / Headless Modernization
            $this->emailSubject = "Scaling {$companyName}'s web platform ({$cms})";
            $this->emailBody = "Hi {$contactName},\n\n"
                . "I noticed {$companyName} is currently running on {$cms}.\n\n"
                . "We recently helped similar teams in the {$this->company->industry} space transition away from monolithic CMS bottlenecks into clean, scalable Laravel & Headless web apps with automated workflows.\n\n"
                . "Open to seeing a quick 3-point benchmark on how we could modernize your backend infrastructure?\n\n"
                . $signature;
        }

        $this->copyStatusMessage = "Draft refreshed with latest company & contact intelligence.";
    }

    public function regenerateEmailCopy(): void
    {
        $this->generateEmailCopy();
        $this->queueOutreachMessage();
        $this->loadCompany();
        session()->flash('success_message', '✓ Email copy regenerated and saved to queue!');
    }

    public function queueOutreachMessage(): void
    {
        $contact = $this->company->contacts->firstWhere('id', $this->selectedContactId);

        OutreachMessage::updateOrCreate(
            [
                'company_id' => $this->company->id,
                'contact_id' => $contact?->id,
            ],
            [
                'segment' => $this->selectedSegment,
                'channel' => 'email',
                'recipient_email' => $contact?->email ?? ('contact@' . $this->company->domain),
                'subject' => $this->emailSubject,
                'body_text' => $this->emailBody,
                'status' => 'queued',
                'staged_at' => now(),
            ]
        );

        // Update opportunity statuses to 'qualified'
        foreach ($this->company->opportunities as $opp) {
            $opp->update(['status' => 'qualified']);
        }

        $this->isQueued = true;
        session()->flash('success_message', 'Cold outreach email saved to queue!');
    }

    public function sendEmailDirectly(): void
    {
        $contact = $this->company->contacts->firstWhere('id', $this->selectedContactId);
        $toEmail = $contact?->email ?? ('contact@' . $this->company->domain);

        try {
            $outreach = OutreachMessage::updateOrCreate(
                [
                    'company_id' => $this->company->id,
                    'contact_id' => $contact?->id,
                ],
                [
                    'segment' => $this->selectedSegment,
                    'channel' => 'email',
                    'recipient_email' => $toEmail,
                    'subject' => $this->emailSubject,
                    'body_text' => $this->emailBody,
                    'status' => 'queued',
                    'staged_at' => now(),
                ]
            );

            // Append 1x1 invisible open tracking pixel
            $trackingUrl = url("/track/open/{$outreach->id}");
            $htmlBody = nl2br(e($this->emailBody)) . "<br><br><img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

            $pdfPath = $this->company->report_pdf_path;
            $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->company->name ?? 'Company');

            \Illuminate\Support\Facades\Mail::html($htmlBody, function ($message) use ($toEmail, $pdfPath, $companyName) {
                $message->to($toEmail)
                        ->subject($this->emailSubject);

                if ($pdfPath && file_exists($pdfPath)) {
                    $message->attach($pdfPath, [
                        'as' => "{$companyName}_Website_Technical_Audit.pdf",
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            $outreach->update([
                'status' => 'delivered',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            // Update opportunity statuses to 'contacted'
            foreach ($this->company->opportunities as $opp) {
                $opp->update(['status' => 'contacted']);
            }

            $this->isQueued = true;
            $this->loadCompany();
            session()->flash('success_message', "🚀 Cold email sent immediately to {$toEmail} with PDF audit report attached!");
        } catch (\Exception $e) {
            session()->flash('error_message', "Failed to send email: " . $e->getMessage());
        }
    }

    public function downloadReportPdf()
    {
        $pdfPath = $this->company->report_pdf_path;
        if (!$pdfPath || !file_exists($pdfPath)) {
            session()->flash('error_message', "No generated PDF audit report found for this company yet. Run the 360° Intelligence pipeline first.");
            return;
        }

        $companyName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->company->name ?? 'Company');
        return response()->download($pdfPath, "{$companyName}_Website_Technical_Audit.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function updateOpportunityStatus(int $oppId, string $newStatus): void
    {
        $opp = Opportunity::find($oppId);
        if ($opp) {
            $opp->update(['status' => $newStatus]);
            $this->loadCompany();
            session()->flash('success_message', "Opportunity status updated to {$newStatus}.");
        }
    }

    public function render()
    {
        return view('livewire.company-detail')->layout('components.layouts.app');
    }
}
