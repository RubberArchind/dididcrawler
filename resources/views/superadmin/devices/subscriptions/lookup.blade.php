@extends('layouts.admin')

@section('title', 'Find Device')
@section('page-title', 'Add Subscription by Device ID')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Locate Device</h5>
            <a href="{{ route('superadmin.devices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to devices
            </a>
        </div>
    <form method="POST" action="{{ route('superadmin.devices.subscriptions.lookup.submit') }}">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label for="device_uid" class="form-label">Device ID</label>
                    <input type="text" name="device_uid" id="device_uid" class="form-control" value="{{ old('device_uid') }}" placeholder="Enter ESP32 unique ID" required>
                </div>
                <p class="text-muted small">We'll redirect you to the subscription form once we locate the device.</p>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>
                    Find Device
                </button>
            </div>
        </form>
    </div>
@endsection
