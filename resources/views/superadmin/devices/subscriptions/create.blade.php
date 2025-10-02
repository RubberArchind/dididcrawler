@extends('layouts.admin')

@section('title', 'Add Subscription')
@section('page-title', 'New Subscription for ' . $device->device_uid)

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Subscription Details</h5>
            <a href="{{ route('superadmin.devices.show', $device) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to device
            </a>
        </div>
        <form method="POST" action="{{ route('superadmin.devices.subscriptions.store', $device) }}">
            @csrf
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="subscription_name" class="form-label">Subscription Name</label>
                        <input type="text" name="subscription_name" id="subscription_name" class="form-control" value="{{ old('subscription_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="plan" class="form-label">Plan</label>
                        <input type="text" name="plan" id="plan" class="form-control" value="{{ old('plan') }}" placeholder="Premium, Basic, etc">
                    </div>
                    <div class="col-md-6">
                        <label for="starts_on" class="form-label">Starts On</label>
                        <input type="date" name="starts_on" id="starts_on" class="form-control" value="{{ old('starts_on') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="ends_on" class="form-label">Ends On</label>
                        <input type="date" name="ends_on" id="ends_on" class="form-control" value="{{ old('ends_on') }}">
                        <div class="form-text">Leave empty for ongoing subscriptions.</div>
                    </div>
                    <div class="col-12">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" rows="4" class="form-control" placeholder="Any additional context or integration details...">{{ old('notes') }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label" for="is_active">Active subscription</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Add Subscription
                </button>
            </div>
        </form>
    </div>
@endsection
