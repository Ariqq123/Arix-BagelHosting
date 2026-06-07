<?php

namespace Pterodactyl\Services\PackHosts;

use Pterodactyl\Contracts\PackHost;
use Pterodactyl\Exceptions\Service\PackHostException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class McpacksDevHost implements PackHost
{
    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    public function upload(string $localPath, string $filename): string
    {
        if (!is_file($localPath) || !is_readable($localPath)) {
            throw new PackHostException('Local pack file is missing or not readable: ' . $localPath);
        }

        $stream = fopen($localPath, 'r');
        if ($stream === false) {
            throw new PackHostException('Failed to open local pack file for reading: ' . $localPath);
        }

        try {
            $response = $this->client->post('https://mcpacks.dev/api/v1/packs', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => $stream,
                        'filename' => $filename,
                    ],
                ],
            ]);
        } catch (GuzzleException $e) {
            fclose($stream);
            throw new PackHostException('Failed to upload pack to mcpacks.dev: ' . $e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new PackHostException('mcpacks.dev returned non-200 status: ' . $response->getStatusCode());
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!isset($data['download_url'])) {
            fclose($stream);
            throw new PackHostException('mcpacks.dev response missing download_url field');
        }

        fclose($stream);
        return $data['download_url'];
    }
}
