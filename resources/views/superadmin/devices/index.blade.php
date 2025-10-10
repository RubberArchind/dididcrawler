@extends('layouts.admin')

@section('title', 'Device Management')
@section('page-title', 'IoT Devices')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('superadmin.devices.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-activity me-1"></i>
                Monitoring Dashboard
            </a>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('superadmin.devices.index') }}" class="d-flex align-items-center gap-2">
                <label for="status" class="form-label mb-0">Status</label>
                <select name="status" id="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="idle" @selected(request('status') === 'idle')>Idle</option>
                    <option value="dead" @selected(request('status') === 'dead')>Dead</option>
                </select>
            </form>
            <a href="{{ route('superadmin.devices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Register Device
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registered Devices</h5>
            <span class="text-muted">{{ $devices->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Subscriptions</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td class="fw-semibold">{{ $device->device_uid }}</td>
                                <td>
                                    @if($device->user)
                                        <div>
                                            <strong>{{ $device->user->name }}</strong>
                                            <div class="text-muted small">{{ $device->user->email }}</div>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($device->tags))
                                        @foreach($device->tags as $tag)
                                            <span class="badge bg-light text-dark border border-1 me-1">#{{ $tag }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'idle' => 'warning',
                                            'dead' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$device->status] ?? 'info' }}">{{ ucfirst($device->status) }}</span>
                                </td>
                                <td>
                                    {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">{{ $device->active_subscriptions_count }} active</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('superadmin.devices.show', $device) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-cpu fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No devices registered yet.</p>
                                    <small>Register your first IoT device to start monitoring activity.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($devices->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $devices->links() }}
            </div>
        @endif
    </div>
@endsection
