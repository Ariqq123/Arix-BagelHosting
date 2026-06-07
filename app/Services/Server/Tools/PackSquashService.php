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
        $extractedDir = null;

        try {
            $outputPath = storage_path("app/server-tools/{$server->uuid}/packsquash/" . uniqid() . '.zip');
            @mkdir(dirname($outputPath), 0755, true);

            // Extract uploaded ZIP to a temporary directory
            $extractedDir = storage_path("app/server-tools/{$server->uuid}/packsquash/extracted_" . uniqid());
            @mkdir($extractedDir, 0755, true);

            if (!file_exists($inputPath)) {
                throw new \RuntimeException("Uploaded file does not exist at path: {$inputPath}");
            }

            if (filesize($inputPath) < 100) {
                throw new \RuntimeException("Uploaded file is too small to be a valid resource pack ZIP.");
            }

            $zip = new \ZipArchive();
            if ($zip->open($inputPath) === true) {
                $zip->extractTo($extractedDir);
                $zip->close();
            } else {
                $errorCode = $zip->status;
                throw new \RuntimeException("Failed to extract uploaded ZIP file. ZipArchive error code: {$errorCode}");
            }

            // Detect if the pack is inside a single top-level folder (very common)
            $entries = array_diff(scandir($extractedDir), ['.', '..']);
            if (count($entries) === 1) {
                $singleEntry = $entries[array_key_first($entries)];
                $singleEntryPath = $extractedDir . '/' . $singleEntry;
                if (is_dir($singleEntryPath) && file_exists($singleEntryPath . '/pack.mcmeta')) {
                    $extractedDir = $singleEntryPath;
                }
            }

            // Generate temporary TOML options file
            $optionsFile = tempnam(sys_get_temp_dir(), 'packsquash_') . '.toml';
            $toml = "pack_directory = \"/input\"\noutput_file_path = \"/output/" . basename($outputPath) . "\"\n";
            file_put_contents($optionsFile, $toml);

            // Docker run with options file mounted
            $process = new Process([
                'sudo', '-u', 'packsquash', '/usr/local/bin/packsquash-docker',
                '--rm',
                '-v', $extractedDir . ':/input:ro',
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
            if ($extractedDir && is_dir($extractedDir)) {
                // Recursive delete of extracted directory
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($extractedDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    @$todo($fileinfo->getRealPath());
                }
                @rmdir($extractedDir);
            }
        }
    }
}
