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
        'max_compression' => ['--compression-level', '9'],
        'balanced' => [],
        'fastest' => ['--compression-level', '1'],
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

        $optionsFile = null;

        try {
            $outputPath = storage_path("app/server-tools/{$server->uuid}/packsquash/" . uniqid() . '.zip');
            @mkdir(dirname($outputPath), 0755, true);

            // Generate temporary TOML options file
            $optionsFile = tempnam(sys_get_temp_dir(), 'packsquash_') . '.toml';
            $toml = "pack_directory = \"/input\"\noutput_file_path = \"/output/" . basename($outputPath) . "\"\n";
            file_put_contents($optionsFile, $toml);

            // Docker run with options file mounted
            $process = new Process([
                'sudo', '-u', 'packsquash', '/usr/local/bin/packsquash-docker',
                '--rm',
                '-v', dirname($inputPath) . ':/input:ro',
                '-v', dirname($outputPath) . ':/output',
                '-v', $optionsFile . ':/config/options.toml:ro',
                'ghcr.io/comunidadaylas/packsquash:latest',
                '/config/options.toml'
            ]);
            $process->setTimeout(300);
            $process->run();

            $stdout = $process->getOutput();
            $stderr = $process->getErrorOutput();
            $combinedLogs = trim($stdout . "\n" . $stderr);

            if (!$process->isSuccessful()) {
                if (str_contains($stderr, 'permission denied while trying to connect to the docker API')) {
                    throw new \RuntimeException(
                        'Docker is not accessible. Please ensure the packsquash system user exists, is in the docker group, and that the sudoers rule is correctly configured.'
                    );
                }

                // Store logs on the run record before throwing
                $run->update([
                    'meta' => array_merge($run->meta ?? [], ['logs' => $combinedLogs]),
                    'error_message' => 'PackSquash process failed. Check logs for details.',
                ]);

                throw new ProcessFailedException($process);
            }

            $uploadResult = $this->packHost->upload($outputPath, basename($outputPath));

            // Handle both string URLs (legacy) and JSON responses with view_url/sha1
            if (is_string($uploadResult) && str_starts_with($uploadResult, '{')) {
                $uploadData = json_decode($uploadResult, true);
                $downloadUrl = $uploadData['download_url'] ?? $uploadResult;
                $viewUrl = $uploadData['view_url'] ?? $downloadUrl;
                $sha1 = $uploadData['sha1'] ?? null;
            } else {
                $downloadUrl = $uploadResult;
                $viewUrl = $downloadUrl;
                $sha1 = null;
            }

            // Compute sha1 locally if not provided by host
            if ($sha1 === null && file_exists($outputPath)) {
                $sha1 = sha1_file($outputPath) ?: null;
            }

            $run->update([
                'output_filename' => basename($outputPath),
                'host_provider' => 'mcpacks_dev',
                'download_url' => $downloadUrl,
                'view_url' => $viewUrl,
                'sha1' => $sha1,
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
        } finally {
            if ($optionsFile && file_exists($optionsFile)) {
                @unlink($optionsFile);
            }
        }
    }
}
