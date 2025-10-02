@extends('layouts.admin')

@section('title', 'Edit Device')
@section('page-title', 'Update Device')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Device</h5>
            <a href="{{ route('superadmin.devices.show', $device) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to device
            </a>
        </div>
        <form id="update-device-form" method="POST" action="{{ route('superadmin.devices.update', $device) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="device_uid" class="form-label">Device ID</label>
                        <input type="text" name="device_uid" id="device_uid" class="form-control" value="{{ old('device_uid', $device->device_uid) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Assigned User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $device->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" name="tags" id="tags" class="form-control" value="{{ old('tags', implode(', ', $device->tags ?? [])) }}" placeholder="farm, greenhouse, sensor">
                    </div>
                </div>
            </div>
        </form>
        <div class="card-footer d-flex justify-content-between">
            <form method="POST" action="{{ route('superadmin.devices.destroy', $device) }}" onsubmit="return confirm('Delete this device? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>
                    Delete Device
                </button>
            </form>
            <button type="submit" form="update-device-form" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>
                Update Device
            </button>
        </div>
    </div>
@endsection
