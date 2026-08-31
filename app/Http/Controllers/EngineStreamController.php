<?php

namespace App\Http\Controllers;

use App\Services\EngineRunnerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EngineStreamController extends Controller
{
    public function stream(Request $request, string $script): StreamedResponse
    {
        $allowedScripts = [
            'run_discovery.py',
            'run_upwork_spider.py',
            'job_feed_discovery.py',
            'run_intelligence.py',
            'run_scoring.py',
            'run_enrichment.py',
            'run_outreach.py',
            'run_offline_copy_batch.py',
            'run_google_maps_crawler.py',
            'clean_duplicate_outreach.py',
            'init_db.py',
            'quick_demo.py',
            'export_leads.py',
        ];

        if (!in_array($script, $allowedScripts)) {
            abort(400, 'Invalid script requested.');
        }

        $runner = new EngineRunnerService();
        $enginePath = $runner->getEnginePath();
        $python = $runner->getPythonBinary();

        $cmd = [$python, '-u', "scripts/{$script}"];

        return new StreamedResponse(function () use ($cmd, $enginePath) {
            // Set unlimited execution time for streaming processes
            set_time_limit(0);
            @ini_set('max_execution_time', '0');

            // Clean existing output buffers without flushing raw text early
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $descriptorspec = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];

            $env = array_merge($_ENV, [
                'PYTHONUNBUFFERED' => '1',
                'ENGINE_PATH' => $enginePath,
            ]);

            $process = proc_open($cmd, $descriptorspec, $pipes, $enginePath, $env);

            if (is_resource($process)) {
                fclose($pipes[0]);

                // Stream stdout and stderr in real-time
                stream_set_blocking($pipes[1], false);
                stream_set_blocking($pipes[2], false);

                while (true) {
                    $read = [$pipes[1], $pipes[2]];
                    $write = null;
                    $except = null;

                    $numChanged = @stream_select($read, $write, $except, 0, 100000); // 100ms timeout

                    if ($numChanged === false) {
                        break;
                    }

                    $hasData = false;

                    foreach ($read as $pipe) {
                        $line = fgets($pipe);
                        if ($line !== false && $line !== '') {
                            $hasData = true;
                            echo "data: " . json_encode(['line' => $line]) . "\n\n";
                            flush();
                        }
                    }

                    $status = proc_get_status($process);
                    if (!$status['running'] && !$hasData) {
                        // Check one final time for remaining buffer
                        while ($line = fgets($pipes[1])) {
                            echo "data: " . json_encode(['line' => $line]) . "\n\n";
                            flush();
                        }
                        while ($line = fgets($pipes[2])) {
                            echo "data: " . json_encode(['line' => $line]) . "\n\n";
                            flush();
                        }
                        $exitCode = $status['exitcode'];
                        echo "data: " . json_encode(['done' => true, 'exit_code' => $exitCode]) . "\n\n";
                        flush();
                        break;
                    }
                }

                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
            } else {
                echo "data: " . json_encode(['line' => "❌ Failed to start process.\n", 'done' => true, 'exit_code' => 1]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
