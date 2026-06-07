<?php

namespace Pterodactyl\Http\Controllers\Server\Tools;

use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\Server\Tools\PackSquashService;
use Illuminate\Http\Request;

class PackSquashController extends Controller
{
    public function __construct(private PackSquashService $service) {}

    public function store(Server $server, Request $request)
    {
        $this->authorize('view', $server);

        $request->validate([
            'pack' => 'required|file|mimes:zip',
            'preset' => 'required|in:max_compression,balanced,fastest,protection',
        ]);

        $path = $request->file('pack')->store("server-tools/{$server->uuid}/input");

        try {
            $run = $this->service->optimize($server, storage_path("app/{$path}"), $request->preset);
            return response()->json($run);
        } catch (\Throwable $e) {
            // Fetch the most recent failed run so we can return logs
            $failedRun = \Pterodactyl\Models\ServerToolRun::where('server_id', $server->id)
                ->where('tool', 'packsquash')
                ->where('status', 'failed')
                ->latest()
                ->first();

            return response()->json([
                'error' => $e->getMessage(),
                'run'   => $failedRun,
            ], 422);
        }
    }
}
