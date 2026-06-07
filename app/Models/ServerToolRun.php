<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerToolRun extends Model
{
    /**
     * The resource name for this model used in events.
     */
    protected static string $resourceName = 'server_tool_run';

    /**
     * The table associated with the model.
     */
    protected $table = 'server_tool_runs';

    /**
     * Fields that are mass assignable.
     */
    protected $fillable = [
        'server_id',
        'tool',
        'preset',
        'input_filename',
        'output_filename',
        'host_provider',
        'host_uuid',
        'download_url',
        'view_url',
        'sha1',
        'original_size',
        'optimized_size',
        'status',
        'error_message',
        'meta',
        'started_at',
        'completed_at',
    ];

    /**
     * Cast parameters to correct data types.
     */
    protected $casts = [
        'server_id' => 'integer',
        'original_size' => 'integer',
        'optimized_size' => 'integer',
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the server that owns this tool run.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Scope a query to only include runs for a specific server.
     */
    public function scopeForServer($query, int $serverId)
    {
        return $query->where('server_id', $serverId);
    }

    /**
     * Scope a query to only include runs for a specific tool.
     */
    public function scopeForTool($query, string $tool)
    {
        return $query->where('tool', $tool);
    }

    /**
     * Scope a query to only include completed runs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed runs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending runs.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
