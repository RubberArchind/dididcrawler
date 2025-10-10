@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-broadcast me-2"></i>
                            MQTT Connection Settings
                        </h5>
                        <span class="badge bg-primary-subtle text-primary">Live</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('superadmin.settings.update') }}" class="row g-3" data-confirm-message="Apply MQTT configuration changes?">
                            @csrf
                            <div class="col-md-6">
                                <label for="mqtt_host" class="form-label">MQTT Host <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mqtt_host') is-invalid @enderror" id="mqtt_host" name="mqtt_host" value="{{ old('mqtt_host', $settings->mqtt_host ?? '') }}" required>
                                @error('mqtt_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="mqtt_port" class="form-label">Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('mqtt_port') is-invalid @enderror" id="mqtt_port" name="mqtt_port" value="{{ old('mqtt_port', $settings->mqtt_port ?? 1883) }}" min="1" required>
                                @error('mqtt_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="mqtt_topic_prefix" class="form-label">Topic Prefix <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mqtt_topic_prefix') is-invalid @enderror" id="mqtt_topic_prefix" name="mqtt_topic_prefix" value="{{ old('mqtt_topic_prefix', $settings->mqtt_topic_prefix ?? 'devices') }}" required>
                                @error('mqtt_topic_prefix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mqtt_username" class="form-label">Username</label>
                                <input type="text" class="form-control @error('mqtt_username') is-invalid @enderror" id="mqtt_username" name="mqtt_username" value="{{ old('mqtt_username', $settings->mqtt_username ?? '') }}">
                                @error('mqtt_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mqtt_password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('mqtt_password') is-invalid @enderror" id="mqtt_password" name="mqtt_password" value="{{ old('mqtt_password', $settings->mqtt_password ?? '') }}">
                                @error('mqtt_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                    <small class="text-muted">Devices should publish to <code>devices/&lt;device_id&gt;/heartbeat</code>.</small>
                                    <pre class="bg-light border rounded small px-3 py-2 mb-0">{
  "device_id": "TEST123",
  "status": "online",
  "timestamp": "2024-01-01T00:00:00Z"
}</pre>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Update MQTT Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-archive me-2"></i>
                            Backup & Maintenance
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-4">
                        <div>
                            <h6 class="fw-semibold">Database Backup</h6>
                            <p class="text-muted small mb-3">Create a downloadable backup of the latest database snapshot.</p>
                            <form method="POST" action="{{ route('superadmin.backup') }}" data-confirm-message="Create a new database backup now?">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-download me-2"></i>
                                    Create Backup
                                </button>
                            </form>
                        </div>

                        <div>
                            <h6 class="fw-semibold">System Maintenance</h6>
                            <p class="text-muted small mb-3">Clear caches and rebuild optimised configuration files.</p>
                            <form method="POST" action="{{ route('superadmin.maintenance') }}" data-confirm-message="This will clear caches and optimise config. Continue?">
                                @csrf
                                <button type="submit" class="btn btn-info text-white">
                                    <i class="bi bi-arrow-repeat me-2"></i>
                                    Clear Cache
                                </button>
                            </form>
                        </div>

                        <div class="border-top pt-3">
                            <h6 class="fw-semibold">Automated Backup Schedule</h6>
                            <form method="POST" action="{{ route('superadmin.settings.update') }}" class="row g-3" data-confirm-message="Update automated backup schedule?">
                                @csrf
                                <div class="col-md-6">
                                    <label for="backup_enabled" class="form-label">Enable Auto Backup</label>
                                    <select class="form-select" id="backup_enabled" name="backup_enabled">
                                        <option value="0" {{ old('backup_enabled', $settings->backup_enabled ?? 0) == 0 ? 'selected' : '' }}>Disabled</option>
                                        <option value="1" {{ old('backup_enabled', $settings->backup_enabled ?? 0) == 1 ? 'selected' : '' }}>Enabled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                    <select class="form-select" id="backup_frequency" name="backup_frequency">
                                        <option value="daily" {{ old('backup_frequency', $settings->backup_frequency ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('backup_frequency', $settings->backup_frequency ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('backup_frequency', $settings->backup_frequency ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="backup_retention" class="form-label">Keep Backups (days)</label>
                                    <input type="number" class="form-control" id="backup_retention" name="backup_retention" value="{{ old('backup_retention', $settings->backup_retention ?? 30) }}" min="1">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Update Backup Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                document.querySelectorAll('.alert').forEach(function (alert) {
                    const bootstrapAlert = new bootstrap.Alert(alert);
                    bootstrapAlert.close();
                });
            }, 5000);

            document.querySelectorAll('form[data-confirm-message]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const message = form.getAttribute('data-confirm-message');
                    if (!confirm(message)) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush