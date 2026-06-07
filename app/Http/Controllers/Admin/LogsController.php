<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\ActivityLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()
            ->where(function ($q) {
                $q->whereHas('actor', function ($sub) {
                    $sub->where('root_admin', true);
                })->orWhereIn('event', ['auth:fail', 'user:error']);
            })
            ->with('actor');

        if ($request->filled('filter.event')) {
            $query->where('event', 'like', $request->input('filter.event') . '%');
        }

        if ($request->filled('filter.search')) {
            $search = $request->input('filter.search');
            $query->where(function ($q) use ($search) {
                $q->where('properties', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter.since')) {
            $query->where('timestamp', '>=', $request->input('filter.since'));
        }

        if ($request->filled('filter.until')) {
            $query->where('timestamp', '<=', $request->input('filter.until'));
        }

        $adminLogs = (clone $query)
            ->whereHas('actor', fn($q) => $q->where('root_admin', true))
            ->orderBy('timestamp', 'desc')
            ->paginate(15, ['*'], 'admin_page');

        $userLogs = (clone $query)
            ->whereIn('event', ['auth:fail', 'user:error'])
            ->whereDoesntHave('actor', fn($q) => $q->where('root_admin', true))
            ->orderBy('timestamp', 'desc')
            ->paginate(15, ['*'], 'user_page');

        return view('admin.logs.index', [
            'adminLogs' => $adminLogs,
            'userLogs' => $userLogs,
        ]);
    }
}
