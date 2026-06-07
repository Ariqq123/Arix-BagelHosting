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
                        <form action="{{ route('admin.logs') }}" method="GET" class="form-inline">
                            <div class="input-group input-group-sm" style="width: 150px; margin-right: 5px;">
                                <input type="text" name="filter[event]" value="{{ request('filter.event') }}" class="form-control pull-right" placeholder="Event (e.g. auth)">
                            </div>
                            <div class="input-group input-group-sm" style="width: 150px; margin-right: 5px;">
                                <input type="text" name="filter[search]" value="{{ request('filter.search') }}" class="form-control pull-right" placeholder="Search IP/properties">
                            </div>
                            <div class="input-group input-group-sm" style="width: 120px; margin-right: 5px;">
                                <input type="date" name="filter[since]" value="{{ request('filter.since') }}" class="form-control">
                            </div>
                            <div class="input-group input-group-sm" style="width: 120px; margin-right: 5px;">
                                <input type="date" name="filter[until]" value="{{ request('filter.until') }}" class="form-control">
                            </div>
                            <div class="input-group input-group-sm">
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