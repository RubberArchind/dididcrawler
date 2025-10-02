@extends('layouts.admin')

@section('title', 'Register Device')
@section('page-title', 'Add IoT Device')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Device Details</h5>
            <a href="{{ route('superadmin.devices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to list
            </a>
        </div>
        <form method="POST" action="{{ route('superadmin.devices.store') }}">
            @csrf
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="device_uid" class="form-label">Device ID</label>
                        <input type="text" name="device_uid" id="device_uid" class="form-control" value="{{ old('device_uid') }}" required>
                        <div class="form-text">Unique identifier from the ESP32 device.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Assigned User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Each device can be linked to one user at a time.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" name="tags" id="tags" class="form-control" value="{{ old('tags') }}" placeholder="farm, greenhouse, sensor">
                        <div class="form-text">Separate tags with commas to help categorize devices.</div>
                    </div>
                </div>
                <div class="alert alert-info mt-4 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Device status is now calculated automatically from its subscriptions.
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Save Device
                </button>
            </div>
        </form>
    </div>
@endsection
