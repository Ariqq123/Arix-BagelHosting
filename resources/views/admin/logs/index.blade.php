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
                        <form action="{{ route('admin.logs') }}" method="GET">
                            <div class="input-group input-group-sm" style="display: inline-block; width: auto; vertical-align: top; margin-right: 5px;">
                                <select name="filter[event]" class="form-control" style="width: 160px;">
                                    <option value="">All Events</option>
                                    <option value="auth" {{ request('filter.event') === 'auth' ? 'selected' : '' }}>auth:*</option>
                                    <option value="user" {{ request('filter.event') === 'user' ? 'selected' : '' }}>user:*</option>
                                    <option value="server" {{ request('filter.event') === 'server' ? 'selected' : '' }}>server:*</option>
                                    <option value="settings" {{ request('filter.event') === 'settings' ? 'selected' : '' }}>settings:*</option>
                                    <option value="node" {{ request('filter.event') === 'node' ? 'selected' : '' }}>node:*</option>
                                </select>
                            </div>
                            <div class="input-group input-group-sm" style="display: inline-block; width: auto; vertical-align: top; margin-right: 5px;">
                                <input type="text" name="filter[search]" value="{{ request('filter.search') }}" class="form-control" style="width: 160px;" placeholder="Search IP / properties">
                            </div>
                            <div class="input-group input-group-sm" style="display: inline-block; width: auto; vertical-align: top; margin-right: 5px;">
                                <input type="date" name="filter[since]" value="{{ request('filter.since') }}" class="form-control" style="width: 130px;">
                            </div>
                            <div class="input-group input-group-sm" style="display: inline-block; width: auto; vertical-align: top; margin-right: 5px;">
                                <input type="date" name="filter[until]" value="{{ request('filter.until') }}" class="form-control" style="width: 130px;">
                            </div>
                            <div class="input-group input-group-sm" style="display: inline-block; width: auto; vertical-align: top;">
                                <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i></button>
                            </div>
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