<?php

namespace Pterodactyl\Services\Server\Tools;

use Pterodactyl\Contracts\PackHost;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\ServerToolRun;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PackSquashService
{
    private PackHost $packHost;

    public function __construct(PackHost $packHost)
    {
        $this->packHost = $packHost;
    }

    private array $presetFlags = [
        'max_compression' => ['--compress-level', '9'],
        'balanced' => [],
        'fastest' => ['--compress-level', '1'],
        'protection' => ['--protect', 'true'],
    ];

    public function optimize(Server $server, string $inputPath, string $preset): ServerToolRun
    {
        $run = ServerToolRun::create([
            'server_id' => $server->id,
            'tool' => 'packsquash',
            'preset' => $preset,
            'input_filename' => basename($inputPath),
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $outputPath = storage_path("app/server-tools/{$server->uuid}/packsquash/" . uniqid() . '.zip');
            @mkdir(dirname($outputPath), 0755, true);

            // Docker run via Symfony Process (safe array-based command execution)
            $flags = $this->presetFlags[$preset] ?? [];
            $process = new Process(array_merge(
                ['docker', 'run', '--rm',
                    '-v', dirname($inputPath) . ':/input',
                    '-v', dirname($outputPath) . ':/output',
                    'packsquash:latest'],
                $flags,
                ['/input/' . basename($inputPath), '/output/' . basename($outputPath)]
            ));
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $downloadUrl = $this->packHost->upload($outputPath, basename($outputPath));

            $run->update([
                'output_filename' => basename($outputPath),
                'host_provider' => 'mcpacks_dev',
                'download_url' => $downloadUrl,
                'status' => 'completed',
                'completed_at' => now(),
                'original_size' => filesize($inputPath),
                'optimized_size' => filesize($outputPath),
            ]);

            return $run;
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }
}
