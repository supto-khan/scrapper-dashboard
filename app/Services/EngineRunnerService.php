<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class EngineRunnerService
{
    /**
     * Absolute path to the signal-engine directory.
     *
     * Set ENGINE_PATH in .env to override.
     * Default: the sibling `signal-engine/` folder next to `signal-dashboard/`.
     */
    public function getEnginePath(): string
    {
        return env('ENGINE_PATH', base_path('../signal-engine'));
    }

    /**
     * Path to the Python binary.
     *
     * Resolution order (first match wins):
     *   1. PYTHON_BINARY env var  (works on any server / any venv).
     *   2. Local Conda env at the default macOS Homebrew Anaconda path.
     *   3. System `python3` as final fallback.
     */
    public function getPythonBinary(): string
    {
        // 1. Explicit override (server / CI / custom virtualenv)
        if ($envPython = env('PYTHON_BINARY')) {
            return $envPython;
        }

        // 2. Default macOS Conda path (local development)
        $condaPython = '/opt/homebrew/anaconda3/envs/signal-engine/bin/python';
        if (file_exists($condaPython)) {
            return $condaPython;
        }

        // 3. System-wide fallback
        return 'python3';
    }

    /**
     * Run a specific signal-engine script and return structured result.
     *
     * @param string $scriptName  e.g. 'run_discovery.py'
     * @param array  $args        Additional CLI arguments
     * @param int    $timeout     Seconds before process is killed (default 300)
     */
    public function runScript(string $scriptName, array $args = [], int $timeout = 300): array
    {
        $enginePath = $this->getEnginePath();
        $python = $this->getPythonBinary();
        $scriptPath = "scripts/{$scriptName}";

        $command = array_merge([$python, $scriptPath], $args);

        $result = Process::path($enginePath)
            ->timeout($timeout)
            ->env([
                'PATH' => getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                'HOME' => getenv('HOME') ?: '/root',
                'PYTHONUNBUFFERED' => '1',
                'ENGINE_PATH' => $enginePath,
                'PYTHONPATH' => $enginePath,
                'DB_HOST' => config('database.connections.mysql.host') ?: env('DB_HOST', '127.0.0.1'),
                'DB_PORT' => (string) (config('database.connections.mysql.port') ?: env('DB_PORT', '3306')),
                'DB_USER' => config('database.connections.mysql.username') ?: env('DB_USERNAME', 'root'),
                'DB_PASSWORD' => (string) (config('database.connections.mysql.password') ?: env('DB_PASSWORD', '')),
                'DB_NAME' => config('database.connections.mysql.database') ?: env('DB_DATABASE', 'nexidant_signal'),
                'IMAP_HOST' => env('IMAP_HOST', env('MAIL_HOST', 'mail.nexidant.com')),
                'IMAP_PORT' => (string) env('IMAP_PORT', '993'),
                'IMAP_USERNAME' => env('IMAP_USERNAME', env('MAIL_USERNAME', 'info@nexidant.com')),
                'IMAP_PASSWORD' => (string) env('IMAP_PASSWORD', env('MAIL_PASSWORD', '')),
                'SMTP_HOST' => env('MAIL_HOST', 'mail.nexidant.com'),
                'SMTP_PORT' => (string) env('MAIL_PORT', '465'),
                'SMTP_USER' => env('MAIL_USERNAME', 'info@nexidant.com'),
                'SMTP_PASSWORD' => (string) env('MAIL_PASSWORD', ''),
            ])
            ->run($command);

        return [
            'success'      => $result->successful(),
            'exit_code'    => $result->exitCode(),
            'output'       => $result->output(),
            'error_output' => $result->errorOutput(),
            'command'      => implode(' ', $command),
            'engine_path'  => $enginePath,
            'python'       => $python,
        ];
    }
}
