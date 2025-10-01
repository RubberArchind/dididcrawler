@extends('layouts.admin')

@section('title', 'System Settings')
@section('page-title', 'System Configuration')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Fee Management Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-percent me-2"></i>
                Fee Management
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.settings.update') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="transaction_fee" class="form-label">
                                Transaction Fee Percentage
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('transaction_fee') is-invalid @enderror" 
                                       id="transaction_fee" 
                                       name="transaction_fee" 
                                       value="{{ old('transaction_fee', $settings->transaction_fee ?? 0) }}" 
                                       step="0.01" 
                                       min="0" 
                                       max="100"
                                       required>
                                <span class="input-group-text">%</span>
                                @error('transaction_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Fee percentage deducted from each successful transaction
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="minimum_payout" class="form-label">
                                Minimum Payout Amount
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" 
                                       class="form-control @error('minimum_payout') is-invalid @enderror" 
                                       id="minimum_payout" 
                                       name="minimum_payout" 
                                       value="{{ old('minimum_payout', $settings->minimum_payout ?? 0) }}" 
                                       min="0"
                                       required>
                                @error('minimum_payout')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Minimum amount required for payout processing
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Fee Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Configuration Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-gear me-2"></i>
                Payment Configuration
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.settings.update') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_processing_time" class="form-label">
                                Payment Processing Time
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="payment_processing_time" 
                                       name="payment_processing_time" 
                                       value="{{ old('payment_processing_time', $settings->payment_processing_time ?? 24) }}" 
                                       min="1">
                                <span class="input-group-text">hours</span>
                            </div>
                            <small class="form-text text-muted">
                                Expected time for payment processing
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="auto_payout" class="form-label">Auto Payout</label>
                            <select class="form-select" id="auto_payout" name="auto_payout">
                                <option value="0" {{ old('auto_payout', $settings->auto_payout ?? 0) == 0 ? 'selected' : '' }}>Disabled</option>
                                <option value="1" {{ old('auto_payout', $settings->auto_payout ?? 0) == 1 ? 'selected' : '' }}>Enabled</option>
                            </select>
                            <small class="form-text text-muted">
                                Automatically process payouts when conditions are met
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payout_schedule" class="form-label">Payout Schedule</label>
                            <select class="form-select" id="payout_schedule" name="payout_schedule">
                                <option value="daily" {{ old('payout_schedule', $settings->payout_schedule ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ old('payout_schedule', $settings->payout_schedule ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ old('payout_schedule', $settings->payout_schedule ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                            <small class="form-text text-muted">
                                Frequency of payout processing
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Payment Config
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- System Information Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-info-circle me-2"></i>
                System Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Application Name:</strong></td>
                            <td>{{ config('app.name') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Environment:</strong></td>
                            <td>
                                <span class="badge bg-{{ config('app.env') === 'production' ? 'success' : 'warning' }}">
                                    {{ strtoupper(config('app.env')) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Debug Mode:</strong></td>
                            <td>
                                <span class="badge bg-{{ config('app.debug') ? 'danger' : 'success' }}">
                                    {{ config('app.debug') ? 'ON' : 'OFF' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Laravel Version:</strong></td>
                            <td>{{ app()->version() }}</td>
                        </tr>
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td>{{ phpversion() }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Database:</strong></td>
                            <td>{{ config('database.default') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Users:</strong></td>
                            <td>{{ \App\Models\User::count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Active Users:</strong></td>
                            <td>{{ \App\Models\User::where('role', 'user')->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Transactions:</strong></td>
                            <td>{{ \App\Models\Transaction::count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Settings Update:</strong></td>
                            <td>{{ $settings->updated_at ? $settings->updated_at->format('d/m/Y H:i:s') : 'Never' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup & Maintenance Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-archive me-2"></i>
                Backup & Maintenance
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Database Backup</h6>
                    <p class="text-muted">Create a backup of the database with current data</p>
                    <form method="POST" action="{{ route('superadmin.backup') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to create a backup?')">
                            <i class="bi bi-download me-2"></i>
                            Create Backup
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h6>System Maintenance</h6>
                    <p class="text-muted">Clear cache and optimize system performance</p>
                    <form method="POST" action="{{ route('superadmin.maintenance') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info" onclick="return confirm('This will clear all caches. Continue?')">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Clear Cache
                        </button>
                    </form>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-12">
                    <h6>Automated Backup Schedule</h6>
                    <form method="POST" action="{{ route('superadmin.settings.update') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="backup_enabled" class="form-label">Enable Auto Backup</label>
                                    <select class="form-select" id="backup_enabled" name="backup_enabled">
                                        <option value="0" {{ old('backup_enabled', $settings->backup_enabled ?? 0) == 0 ? 'selected' : '' }}>Disabled</option>
                                        <option value="1" {{ old('backup_enabled', $settings->backup_enabled ?? 0) == 1 ? 'selected' : '' }}>Enabled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                    <select class="form-select" id="backup_frequency" name="backup_frequency">
                                        <option value="daily" {{ old('backup_frequency', $settings->backup_frequency ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('backup_frequency', $settings->backup_frequency ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('backup_frequency', $settings->backup_frequency ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="backup_retention" class="form-label">Keep Backups (days)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="backup_retention" 
                                           name="backup_retention" 
                                           value="{{ old('backup_retention', $settings->backup_retention ?? 30) }}" 
                                           min="1">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Backup Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Form validation and feedback
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bootstrapAlert = new bootstrap.Alert(alert);
                bootstrapAlert.close();
            });
        }, 5000);

        // Form submission confirmation for critical settings
        const forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            if (form.action.includes('backup') || form.action.includes('maintenance')) {
                return; // Skip backup/maintenance forms as they have inline confirmations
            }
            
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to update these settings?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush