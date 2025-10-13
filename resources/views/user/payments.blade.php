@extends('layouts.admin')

@section('title', 'My Payments')
@section('page-title', 'Payment History')

@section('content')
    <!-- Month Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.payments') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Select Month</label>
                    <input type="month" class="form-control" id="month" name="month" value="{{ $month }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>
                        Filter
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <small class="text-muted">Showing data for: @tz(\Carbon\Carbon::parse($month), 'F Y')</small>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $monthly_stats['total_payments'] }}</h4>
                    <p class="mb-0">Total Payments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $monthly_stats['paid_count'] }}</h4>
                    <p class="mb-0">Paid</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body text-center">
                    <h4>Rp {{ number_format($monthly_stats['total_amount'], 0, ',', '.') }}</h4>
                    <p class="mb-0">Total Earned</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <h4>Rp {{ number_format($monthly_stats['paid_amount'], 0, ',', '.') }}</h4>
                    <p class="mb-0">Amount Paid</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Payment Account Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Account Name:</strong></td>
                            <td>{{ auth()->user()->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Account Number:</strong></td>
                            <td><code>{{ auth()->user()->account_number ?: 'Not set' }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Bank/Provider:</strong></td>
                            <td>{{ auth()->user()->bank_name ?: 'Not specified' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ auth()->user()->phone_number ?: 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ auth()->user()->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Member Since:</strong></td>
                            <td>@tz(auth()->user()->created_at, 'd F Y')</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if(!auth()->user()->account_number)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Please update your account number in your profile to receive payments.
                    <a href="{{ route('profile.edit') }}" class="alert-link">Update Profile</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment History -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Payment History</h5>
            @if($payments->isNotEmpty())
                <span class="badge bg-info">{{ $payments->count() }} payments</span>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Period</th>
                            <th>Total Omset</th>
                            <th>Fee Deducted</th>
                            <th>Net Amount</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <strong>@tz($payment->payment_date, 'd/m/Y')</strong>
                                </td>
                                <td>
                                    <small class="text-muted">@tz($payment->payment_date, 'd M Y')</small>
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($payment->total_omset, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <span class="text-warning">Rp {{ number_format($payment->total_fee, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <strong class="text-success">Rp {{ number_format($payment->net_amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($payment->paid_amount > 0)
                                        <span class="text-primary">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->status === 'paid')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Paid
                                        </span>
                                    @elseif($payment->status === 'partial')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>
                                            Partial
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-hourglass me-1"></i>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->notes)
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#notesModal{{ $payment->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Notes Modal -->
                            @if($payment->notes)
                            <div class="modal fade" id="notesModal{{ $payment->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Payment Notes - @tz($payment->payment_date, 'd/m/Y')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <strong>Payment Amount:</strong><br>
                                                    Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}
                                                </div>
                                                <div class="col-6">
                                                        <strong>Paid At:</strong><br>
                                                        {{ $payment->paid_at ? \App\Support\Tz::format($payment->paid_at, 'd/m/Y H:i:s') : '-' }}
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Notes:</strong><br>
                                                <div class="bg-light p-3 rounded">
                                                    {{ $payment->notes }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-wallet fs-1"></i>
                                        <p class="mt-2">No payments found for @tz(\Carbon\Carbon::parse($month), 'F Y')</p>
                                        <p class="small">Payments are processed daily based on your successful transactions.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->isNotEmpty())
                <!-- Payment Summary -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>{{ $payments->where('status', 'paid')->count() }}</h5>
                                <small class="text-success">Paid Payments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>{{ $payments->where('status', 'pending')->count() }}</h5>
                                <small class="text-warning">Pending Payments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('net_amount'), 0, ',', '.') }}</h5>
                                <small class="text-muted">Total Earned</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('paid_amount'), 0, ',', '.') }}</h5>
                                <small class="text-success">Total Received</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Balance Alert -->
                @php
                    $outstanding = $payments->sum('net_amount') - $payments->sum('paid_amount');
                @endphp
                @if($outstanding > 0)
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle me-2"></i>Outstanding Balance</h6>
                        <p class="mb-0">
                            You have an outstanding balance of <strong>Rp {{ number_format($outstanding, 0, ',', '.') }}</strong> 
                            that will be processed in the next payment cycle.
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when month changes
    document.getElementById('month').addEventListener('change', function() {
        this.form.submit();
    });

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush