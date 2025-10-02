@extends('layouts.admin')

@section('title', 'Device Monitoring')
@section('page-title', 'Devices Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('superadmin.devices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to devices
        </a>
        <a href="{{ route('superadmin.devices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Register Device
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-uppercase small mb-1">Total Devices</p>
                            <h3 class="mb-0">{{ $totalDevices }}</h3>
                        </div>
                        <i class="bi bi-cpu fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-uppercase small mb-1">Assigned</p>
                            <h3 class="mb-0">{{ $assignedDevices }}</h3>
                        </div>
                        <i class="bi bi-person-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-uppercase small mb-1">Unassigned</p>
                            <h3 class="mb-0">{{ $unassignedDevices }}</h3>
                        </div>
                        <i class="bi bi-box fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-uppercase small mb-1">Active Subscriptions</p>
                            <h3 class="mb-0">{{ $activeSubscriptions }}</h3>
                        </div>
                        <i class="bi bi-broadcast fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Status Breakdown</h5>
                    <span class="text-muted small">Real-time snapshot</span>
                </div>
                <div class="card-body">
                    @if($statusBreakdown->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($statusBreakdown as $status => $count)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-capitalize">{{ $status }}</span>
                                    <span class="badge bg-primary-subtle text-primary">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                            <p class="mb-0">No device data available yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Heartbeats</h5>
                    <span class="text-muted small">Last seen updates</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>User</th>
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $device)
                                    <tr>
                                        <td>{{ $device->device_uid }}</td>
                                        <td>{{ $device->user?->name ?? 'Unassigned' }}</td>
                                        <td>{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'No data' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No recent activity available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Expiring Soon</h5>
            <span class="text-muted small">Within 14 days</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Subscription</th>
                            <th>Ends On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expiringSoon as $subscription)
                            <tr>
                                <td>{{ $subscription->device->device_uid }}</td>
                                <td>{{ $subscription->subscription_name }}</td>
                                <td>{{ $subscription->ends_on?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">Expiring</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No subscriptions expiring soon.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
