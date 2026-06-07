<?php

namespace App\Services\PackHosts;

use App\Contracts\PackHost;
use App\Exceptions\PackHostException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class McpacksDevHost implements PackHost
{
    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    public function upload(string $localPath, string $filename): string
    {
        try {
            $response = $this->client->post('https://mcpacks.dev/api/v1/packs', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($localPath, 'r'),
                        'filename' => $filename,
                    ],
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new PackHostException('Failed to upload pack to mcpacks.dev: ' . $e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new PackHostException('mcpacks.dev returned non-200 status: ' . $response->getStatusCode());
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!isset($data['download_url'])) {
            throw new PackHostException('mcpacks.dev response missing download_url field');
        }

        return $data['download_url'];
    }
}
