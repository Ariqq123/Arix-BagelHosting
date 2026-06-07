@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content-header')
    <h1>Admin Activity Logs</h1>
@endsection

@section('content')
    <div class="row">
        {{-- Admin Actions --}}
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i data-lucide="shield"></i> Admin Actions</h3>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Admin</th>
                                <th>Event</th>
                                <th>Target</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminLogs as $log)
                                <tr>
                                    <td>{{ $log->timestamp }}</td>
                                    <td>{{ $log->actor?->username ?? 'System' }}</td>
                                    <td><code>{{ $log->event }}</code></td>
                                    <td>{{ $log->subject_type ? class_basename($log->subject_type).'#'.$log->subject_id : '-' }}</td>
                                    <td>{{ $log->ip }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No admin actions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($adminLogs->hasPages())
                    <div class="box-footer with-border">
                        {{ $adminLogs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- User Errors & Failed Logins --}}
        <div class="col-xs-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i data-lucide="alert-triangle"></i> User Errors & Failed Logins</h3>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User / Attempt</th>
                                <th>Event</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userLogs as $log)
                                <tr>
                                    <td>{{ $log->timestamp }}</td>
                                    <td>{{ $log->actor?->username ?? ($log->properties['email'] ?? 'Unknown') }}</td>
                                    <td><code>{{ $log->event }}</code></td>
                                    <td>{{ $log->ip }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No user errors found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($userLogs->hasPages())
                    <div class="box-footer with-border">
                        {{ $userLogs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

<style>
    .logs-filter {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 120px;
    }
    .filter-label {
        font-size: 11px;
        font-weight: 600;
        color: #555;
        margin-bottom: 3px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .filter-actions {
        margin-bottom: 1px;
    }
    .filter-actions .btn {
        height: 30px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>