@extends('layouts.admin')
@php use Illuminate\Support\Str; @endphp

@section('title', 'Device: ' . $device->device_uid)
@section('page-title', 'Device Overview')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('superadmin.devices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to devices
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.devices.dashboard') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-activity"></i> Monitoring
            </a>
            <a href="{{ route('superadmin.devices.edit', $device) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Edit Device
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Device Profile</h5>
                    <span class="badge bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'maintenance' ? 'warning text-dark' : 'secondary') }}">{{ ucfirst($device->status) }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Device ID</dt>
                        <dd class="col-sm-8">{{ $device->device_uid }}</dd>

                        <dt class="col-sm-4">Assigned User</dt>
                        <dd class="col-sm-8">
                            @if($device->user)
                                <div class="d-flex flex-column">
                                    <strong>{{ $device->user->name }}</strong>
                                    <small class="text-muted">{{ $device->user->email }}</small>
                                </div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Unassigned</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Tags</dt>
                        <dd class="col-sm-8">
                            @if(!empty($device->tags))
                                @foreach($device->tags as $tag)
                                    <span class="badge bg-light text-dark border border-1 me-1 mb-1">#{{ $tag }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Not tagged</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Last Seen</dt>
                        <dd class="col-sm-8">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'No heartbeat yet' }}</dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $device->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Quick Reassignment</h5>
                </div>
                <form method="POST" action="{{ route('superadmin.devices.update', $device) }}" class="card-body">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Assigned User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $device->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" name="tags" id="tags" class="form-control" value="{{ old('tags', implode(', ', $device->tags ?? [])) }}" placeholder="farm, greenhouse, sensor">
                        <div class="form-text">Comma separated values.</div>
                    </div>
                    <div>
                        <label for="device_uid" class="form-label">Device ID</label>
                        <input type="text" name="device_uid" id="device_uid" class="form-control" value="{{ old('device_uid', $device->device_uid) }}" required>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Status updates automatically based on active subscriptions.
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Subscriptions</h5>
            <a href="{{ route('superadmin.devices.subscriptions.create', $device) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Add Subscription
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Plan</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($device->subscriptions as $subscription)
                            <tr>
                                <td class="fw-semibold">{{ $subscription->subscription_name }}</td>
                                <td>{{ $subscription->plan ?? '—' }}</td>
                                <td>
                                    @if($subscription->starts_on || $subscription->ends_on)
                                        <div>{{ $subscription->starts_on?->format('d M Y') ?? '—' }} &rarr; {{ $subscription->ends_on?->format('d M Y') ?? '∞' }}</div>
                                    @else
                                        <span class="text-muted">Open-ended</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $subscription->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $subscription->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $subscription->notes ? Str::limit($subscription->notes, 60) : '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('superadmin.devices.subscriptions.toggle', $subscription) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            {{ $subscription->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('superadmin.devices.subscriptions.destroy', $subscription) }}" class="d-inline" onsubmit="return confirm('Remove this subscription?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-broadcast-pin fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No subscriptions found for this device.</p>
                                    <small>Add the first subscription to start tracking services.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
