@extends('layouts.admin')

@section('title', 'Payment Management')
@section('page-title', 'Daily Payment Management')

@push('styles')
<style>
    .status-pill {
        --status-percentage: 0;
        --status-color: currentColor;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.4rem 0.85rem 0.4rem 0.45rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        backdrop-filter: blur(8px);
        box-shadow: 0 12px 30px -12px rgba(0, 0, 0, 0.5);
        color: rgba(255, 255, 255, 0.9);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .status-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 38px -14px rgba(0, 0, 0, 0.6);
    }

    .status-pill__progress {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 50%;
        position: relative;
    }

    .status-pill__progress::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: conic-gradient(var(--status-color) calc(var(--status-percentage) * 1%), rgba(255, 255, 255, 0.2) 0);
        filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.15));
    }

    .status-pill__progress::after {
        content: attr(data-percentage) '%';
        position: absolute;
        inset: 0.4rem;
        border-radius: 50%;
        background: #fff;
        display: grid;
        place-items: center;
        font-weight: 700;
        color: var(--status-color);
        font-size: 0.85rem;
        letter-spacing: 0.02em;
        box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.35);
    }

    .status-pill__value {
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
    }

    .status-pill--success {
        --status-color: #34d399;
    }

    .status-pill--warning {
        --status-color: #fbbf24;
    }

    .status-pill--danger {
        --status-color: #f87171;
    }

    @media (max-width: 768px) {
        .status-pill {
            gap: 0.5rem;
            padding: 0.3rem 0.65rem 0.3rem 0.35rem;
        }

        .status-pill__progress {
            width: 2.2rem;
            height: 2.2rem;
        }

        .status-pill__progress::after {
            inset: 0.32rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.payments') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $date->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>
                        Filter
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <small class="text-muted">Showing payments for: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</small>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Rp {{ number_format($payments->sum('total_omset'), 0, ',', '.') }}</h4>
                            <p class="mb-0">Total Omset</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cash-stack fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Rp {{ number_format($payments->sum('total_fee'), 0, ',', '.') }}</h4>
                            <p class="mb-0">Total Fees</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-percent fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Rp {{ number_format($payments->sum('net_amount'), 0, ',', '.') }}</h4>
                            <p class="mb-0">Net Amount</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Rp {{ number_format($payments->sum('paid_amount'), 0, ',', '.') }}</h4>
                            <p class="mb-0">Total Paid</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-credit-card fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daily Payments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact Info</th>
                            <th>Total Omset</th>
                            <th>Fee Deducted</th>
                            <th>Net Amount</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $payment->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $payment->user->username }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small>{{ $payment->user->email }}</small>
                                        <br>
                                        <small class="text-muted">{{ $payment->user->phone_number }}</small>
                                        <br>
                                        <small class="text-muted">{{ $payment->user->account_number }}</small>
                                    </div>
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
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Paid</span>
                                    @elseif($payment->status === 'partial')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i> Partial</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-clock me-1"></i> Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($payment->status !== 'paid')
                                            <button type="button" class="btn btn-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#paymentModal{{ $payment->id }}">
                                                <i class="bi bi-credit-card"></i>
                                                Pay
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#paymentModal{{ $payment->id }}">
                                                <i class="bi bi-eye"></i>
                                                View
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @push('modals')
                                <!-- Payment Modal -->
                                <div class="modal fade" id="paymentModal{{ $payment->id }}" tabindex="-1" aria-labelledby="paymentModalLabel{{ $payment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="paymentModalLabel{{ $payment->id }}">
                                                    <i class="bi bi-credit-card me-2"></i>
                                                    {{ $payment->status === 'paid' ? 'Payment Details' : 'Record Payment' }} - {{ $payment->user->name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="{{ route('superadmin.payments.pay', $payment) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-sm-6">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-calendar-date me-2 text-primary"></i>
                                                                <span class="fw-medium">Payment Date:</span>
                                                            </div>
                                                            <div class="ps-4">{{ $payment->payment_date->format('d/m/Y') }}</div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="bi bi-bank me-2 text-primary"></i>
                                                                <span class="fw-medium">Account Number:</span>
                                                            </div>
                                                            <div class="ps-4">{{ $payment->user->account_number }}</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card mb-3 border">
                                                        <div class="card-header bg-light py-2">
                                                            <h6 class="mb-0">Payment Summary</h6>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <table class="table table-bordered mb-0">
                                                                <tr>
                                                                    <td class="bg-light w-50"><strong>Total Omset:</strong></td>
                                                                    <td class="text-end">Rp {{ number_format($payment->total_omset, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="bg-light"><strong>Fee Deducted:</strong></td>
                                                                    <td class="text-end text-warning">- Rp {{ number_format($payment->total_fee, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr class="table-success">
                                                                    <td class="bg-light"><strong>Net Amount:</strong></td>
                                                                    <td class="text-end"><strong>Rp {{ number_format($payment->net_amount, 0, ',', '.') }}</strong></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    @if($payment->status !== 'paid')
                                                        <div class="mb-3">
                                                            <label for="paid_amount{{ $payment->id }}" class="form-label fw-medium">
                                                                <i class="bi bi-cash me-1 text-success"></i>
                                                                Amount to Pay
                                                            </label>
                                                            <div class="input-group mb-2">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       id="paid_amount{{ $payment->id }}" 
                                                                       name="paid_amount" 
                                                                       step="0.01" 
                                                                       max="{{ $payment->net_amount }}"
                                                                       value="{{ $payment->net_amount - $payment->paid_amount }}"
                                                                       required>
                                                            </div>
                                                            <div class="form-text">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                Remaining: Rp {{ number_format($payment->net_amount - $payment->paid_amount, 0, ',', '.') }}
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="notes{{ $payment->id }}" class="form-label fw-medium">
                                                                <i class="bi bi-pencil me-1"></i>
                                                                Payment Notes
                                                            </label>
                                                            <textarea class="form-control" 
                                                                      id="notes{{ $payment->id }}" 
                                                                      name="notes" 
                                                                      rows="3" 
                                                                      placeholder="Transfer details, reference number, etc...">{{ $payment->notes }}</textarea>
                                                        </div>
                                                    @else
                                                        <div class="alert alert-success d-flex align-items-start">
                                                            <i class="bi bi-check-circle-fill fs-4 me-2 mt-1"></i>
                                                            <div>
                                                                <h6 class="mb-1 fw-bold">Payment Completed!</h6>
                                                                <div class="mb-1">Paid: <strong>Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</strong></div>
                                                                <div class="mb-1">Date: {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : '-' }}</div>
                                                                @if($payment->notes)
                                                                    <div class="mt-2 pt-2 border-top">
                                                                        <strong>Notes:</strong><br>
                                                                        {{ $payment->notes }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-x me-1"></i> Close
                                                    </button>
                                                    @if($payment->status !== 'paid')
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Record Payment
                                                        </button>
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endpush
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-credit-card fs-1"></i>
                                        <p class="mt-2">No payments found for {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
                                        <p class="small">Payments are automatically generated when there are successful transactions for the day.</p>
                                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-primary">
                                            <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
    
    <!-- Payment Status Summary -->
    @if($payments->isNotEmpty())
        @php
            $paidCount = $payments->where('status', 'paid')->count();
            $paidPercentage = $payments->count() > 0 ? round(($paidCount / $payments->count()) * 100) : 0;
            
            $partialCount = $payments->where('status', 'partial')->count();
            $partialPercentage = $payments->count() > 0 ? round(($partialCount / $payments->count()) * 100) : 0;
            
            $pendingCount = $payments->where('status', 'pending')->count();
            $pendingPercentage = $payments->count() > 0 ? round(($pendingCount / $payments->count()) * 100) : 0;
        @endphp
        
        <!-- Status Summary Stats -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card border-0 bg-success text-white">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <i class="bi bi-check-circle fs-3"></i>
                            <div class="status-pill status-pill--success" style="--status-percentage: {{ $paidPercentage }};">
                                <span class="status-pill__progress" data-percentage="{{ $paidPercentage }}" aria-hidden="true"></span>
                                <span class="status-pill__value">{{ $paidPercentage }}%</span>
                            </div>
                        </div>
                        <h4>{{ $paidCount }}</h4>
                        <p class="mb-0">Paid Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-warning text-white">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <i class="bi bi-dash-circle fs-3"></i>
                            <div class="status-pill status-pill--warning" style="--status-percentage: {{ $partialPercentage }};">
                                <span class="status-pill__progress" data-percentage="{{ $partialPercentage }}" aria-hidden="true"></span>
                                <span class="status-pill__value">{{ $partialPercentage }}%</span>
                            </div>
                        </div>
                        <h4>{{ $partialCount }}</h4>
                        <p class="mb-0">Partial Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-danger text-white">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <i class="bi bi-clock fs-3"></i>
                            <div class="status-pill status-pill--danger" style="--status-percentage: {{ $pendingPercentage }};">
                                <span class="status-pill__progress" data-percentage="{{ $pendingPercentage }}" aria-hidden="true"></span>
                                <span class="status-pill__value">{{ $pendingPercentage }}%</span>
                            </div>
                        </div>
                        <h4>{{ $pendingCount }}</h4>
                        <p class="mb-0">Pending Payments</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Auto-submit form when date changes for better UX
    const dateInput = document.getElementById('date');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle paid amount validation
        document.querySelectorAll('[id^="paymentModal"]').forEach(function(modalEl) {
            var paidAmountInput = modalEl.querySelector('input[name="paid_amount"]');
            if (paidAmountInput) {
                var maxAmount = parseFloat(paidAmountInput.getAttribute('max'));
                paidAmountInput.addEventListener('input', function() {
                    var value = parseFloat(this.value);
                    if (value > maxAmount) {
                        this.value = maxAmount;
                    }
                });
            }
        });
    });
    
    function viewUserTransactions(userId, date) {
        // This would typically open a modal or navigate to detailed view
        alert(`View transactions for User ID: ${userId} on date: ${date}`);
        // You can implement a detailed transaction modal here
    }
</script>
@endpush