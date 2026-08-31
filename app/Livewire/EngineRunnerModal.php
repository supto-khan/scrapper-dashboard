<?php

namespace App\Livewire;

use Livewire\Component;

class EngineRunnerModal extends Component
{
    public bool $isOpen = false;
    public string $activeScript = '';
    public string $scriptTitle = '';
    public bool $isRunning = false;
    public string $output = '';
    public bool $lastSuccess = true;

    protected $listeners = [
        'triggerEngineScript' => 'openAndRun',
        'engineProcessFinished' => 'finishRun',
    ];

    public function openAndRun(string $scriptName, string $title = ''): void
    {
        $this->activeScript = $scriptName;
        $this->scriptTitle = $title ?: $scriptName;
        $this->isOpen = true;
        $this->isRunning = true;
        $this->output = "🚀 Starting `{$scriptName}` from Laravel UI...\nConnecting to Signal Engine environment...\n\n";

        // Dispatch browser event to connect EventSource stream
        $this->dispatch('start-engine-stream', script: $scriptName);
    }

    public function finishRun(bool $success): void
    {
        $this->isRunning = false;
        $this->lastSuccess = $success;
        $this->dispatch('refreshDashboard');
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->isRunning = false;
        $this->output = '';
    }

    public function render()
    {
        return view('livewire.engine-runner-modal');
    }
}
