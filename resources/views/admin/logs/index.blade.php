@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content-header')
    <h1>Admin Activity Logs</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Logs</h3>
                    <div class="box-tools search01">
                        <form action="{{ route('admin.logs') }}" method="GET" class="logs-filter">
                            <div class="filter-group">
                                <label class="filter-label">Event</label>
                                <select name="filter[event]" class="form-control input-sm">
                                    <option value="">All</option>
                                    <option value="auth" {{ request('filter.event') === 'auth' ? 'selected' : '' }}>auth:*</option>
                                    <option value="user" {{ request('filter.event') === 'user' ? 'selected' : '' }}>user:*</option>
                                    <option value="server" {{ request('filter.event') === 'server' ? 'selected' : '' }}>server:*</option>
                                    <option value="settings" {{ request('filter.event') === 'settings' ? 'selected' : '' }}>settings:*</option>
                                    <option value="node" {{ request('filter.event') === 'node' ? 'selected' : '' }}>node:*</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <input type="text" name="filter[search]" value="{{ request('filter.search') }}" class="form-control input-sm" placeholder="IP / properties">
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">From</label>
                                <input type="date" name="filter[since]" value="{{ request('filter.since') }}" class="form-control input-sm">
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">To</label>
                                <input type="date" name="filter[until]" value="{{ request('filter.until') }}" class="form-control input-sm">
                            </div>
                            <div class="filter-group filter-actions">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i data-lucide="filter"></i> <span>Filter</span>
                                </button>
                            </div>
                        </form>
                    </div>

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
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->timestamp }}</td>
                                    <td>{{ $log->actor?->username ?? 'System' }}</td>
                                    <td><code>{{ $log->event }}</code></td>
                                    <td>{{ $log->subject_type ? class_basename($log->subject_type).'#'.$log->subject_id : '-' }}</td>
                                    <td>{{ $log->ip }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                    <div class="box-footer with-border">
                        {{ $logs->appends(request()->query())->render() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection