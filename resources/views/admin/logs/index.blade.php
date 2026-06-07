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
                    <div class="box-tools">
                        <form action="{{ route('admin.logs') }}" method="GET" class="logs-filter">
                            <div class="filter-group">
                                <select name="filter[event]" class="form-control input-sm">
                                    <option value="">All Events</option>
                                    <option value="auth" {{ request('filter.event') === 'auth' ? 'selected' : '' }}>auth:*</option>
                                    <option value="user" {{ request('filter.event') === 'user' ? 'selected' : '' }}>user:*</option>
                                    <option value="server" {{ request('filter.event') === 'server' ? 'selected' : '' }}>server:*</option>
                                    <option value="settings" {{ request('filter.event') === 'settings' ? 'selected' : '' }}>settings:*</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <input type="text" name="filter[search]" value="{{ request('filter.search') }}" class="form-control input-sm" placeholder="Search">
                            </div>
                            <div class="filter-group">
                                <input type="date" name="filter[since]" value="{{ request('filter.since') }}" class="form-control input-sm">
                            </div>
                            <div class="filter-group">
                                <input type="date" name="filter[until]" value="{{ request('filter.until') }}" class="form-control input-sm">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i></button>
                        </form>
                    </div>
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