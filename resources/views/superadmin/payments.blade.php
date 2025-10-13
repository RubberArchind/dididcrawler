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
        color: rgba(255, 255, 255, 0.9);
        box-shadow: 0 12px 30px -12px rgba(0, 0, 0, 0.5);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .status-pill:hover { transform: translateY(-2px); box-shadow: 0 16px 38px -14px rgba(0, 0, 0, 0.6); }
    .status-pill__progress {
        width: 2.6rem; height: 2.6rem; border-radius: 50%; position: relative;
    }
    .status-pill__progress::before { content: ""; position: absolute; inset: 0; border-radius: 50%; background: conic-gradient(var(--status-color) calc(var(--status-percentage) * 1%), rgba(255, 255, 255, 0.2) 0); filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.15)); }
    .status-pill__progress::after { content: attr(data-percentage) '%'; position: absolute; inset: 0.4rem; border-radius: 50%; background: #fff; display: grid; place-items: center; font-weight: 700; color: var(--status-color); font-size: 0.85rem; letter-spacing: 0.02em; }
    .status-pill__value { font-size: 0.9rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25); }
    .status-pill--success { --status-color: #34d399; }
    .status-pill--warning { --status-color: #fbbf24; }
    .status-pill--danger { --status-color: #f87171; }
    @media (max-width: 768px) {
        .status-pill { gap: 0.5rem; padding: 0.3rem 0.65rem 0.3rem 0.35rem; }
        .status-pill__progress { width: 2.2rem; height: 2.2rem; }
        .status-pill__progress::after { inset: 0.32rem; font-size: 0.75rem; }
    }
</style>
@endpush

@section('content')
    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.payments') }}" class="row align-items-end g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label fw-bold">
                        <i class="bi bi-calendar3 me-2"></i>Select Date
                    </label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $date->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <div class="text-muted">
                        <div><strong>Showing payments for:</strong> @tz($date, 'd F Y')</div>
                        @if($cutoffTime)
                            <small>Last payment cutoff: @tz($cutoffTime, 'd/m/Y H:i:s') WIB</small>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Omset</h6>
                            <h4 class="mb-0">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-dark-50 mb-2">Total Fees</h6>
                            <h4 class="mb-0">Rp {{ number_format($stats['total_fee'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="bi bi-percent"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">Paid Amount</h6>
                            <h4 class="mb-0">Rp {{ number_format($stats['paid_net'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">Unpaid Amount</h6>
                            <h4 class="mb-0">Rp {{ number_format($stats['unpaid_net'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Payments Grouped -->
    @forelse($usersWithTransactions as $user)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle me-2 text-primary"></i>
                            {{ $user->name }}
                            <small class="text-muted">({{ $user->username }})</small>
                        </h5>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-envelope me-2"></i>{{ $user->email }}
                            @if($user->phone_number)
                                <i class="bi bi-telephone ms-3 me-2"></i>{{ $user->phone_number }}
                            @endif
                            @if($user->account_number)
                                <i class="bi bi-bank ms-3 me-2"></i>{{ $user->account_number }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex justify-content-end gap-3">
                            <div>
                                <small class="text-muted d-block">Total Omset</small>
                                <strong class="text-primary">Rp {{ number_format($user->total_omset, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Fees</small>
                                <strong class="text-warning">Rp {{ number_format($user->total_fee ?? 0, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Net Amount</small>
                                <strong class="text-success">Rp {{ number_format($user->total_net ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Paid Transactions -->
                @if(($user->paid_group['count'] ?? 0) > 0)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <span class="badge bg-success me-2">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Paid Transactions
                                </span>
                                <small class="text-muted">({{ $user->paid_group['count'] }}) transactions)</small>
                            </h6>
                            <div class="text-end">
                                <small class="text-muted d-block">Paid Net Total</small>
                                <strong class="text-success">Rp {{ number_format($user->paid_group['net'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>Time</th>
                                        <th>Transaction ID</th>
                                        <th>Device</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Fee</th>
                                        <th class="text-end">Net Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($user->paid_group['transactions'] ?? collect()) as $transaction)
                                        <tr>
                                            <td>@tz($transaction->paid_at, 'H:i:s')</td>
                                            <td>
                                                <code class="small">{{ $transaction->transaction_id }}</code>
                                            </td>
                                            <td>
                                                @if($transaction->device)
                                                    <span class="badge bg-info">{{ $transaction->device->device_uid }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                            <td class="text-end text-warning">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</td>
                                            <td class="text-end text-success fw-bold">Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-success">
                                    <tr>
                                        <th colspan="3" class="text-end">Subtotal (Paid):</th>
                                        <th class="text-end">Rp {{ number_format($user->paid_group['omset'] ?? 0, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->paid_group['fee'] ?? 0, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->paid_group['net'] ?? 0, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Unpaid Transactions -->
                @if(($user->unpaid_group['count'] ?? 0) > 0)
                    <div class="mb-3 text-end">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $user->id }}">
                            <i class="bi bi-credit-card"></i> Pay
                        </button>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <span class="badge bg-danger me-2">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Unpaid Transactions
                                </span>
                                <small class="text-muted">({{ $user->unpaid_group['count'] }}) transactions)</small>
                            </h6>
                            <div class="text-end">
                                <small class="text-muted d-block">Unpaid Net Total</small>
                                <strong class="text-danger">Rp {{ number_format($user->unpaid_group['net'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Time</th>
                                        <th>Transaction ID</th>
                                        <th>Device</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Fee</th>
                                        <th class="text-end">Net Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($user->unpaid_group['transactions'] ?? collect()) as $transaction)
                                        <tr>
                                            <td>@tz($transaction->paid_at, 'H:i:s')</td>
                                            <td>
                                                <code class="small">{{ $transaction->transaction_id }}</code>
                                            </td>
                                            <td>
                                                @if($transaction->device)
                                                    <span class="badge bg-info">{{ $transaction->device->device_uid }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                            <td class="text-end text-warning">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</td>
                                            <td class="text-end text-danger fw-bold">Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-danger">
                                    <tr>
                                        <th colspan="3" class="text-end">Subtotal (Unpaid):</th>
                                        <th class="text-end">Rp {{ number_format($user->unpaid_group['omset'] ?? 0, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->unpaid_group['fee'] ?? 0, 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->unpaid_group['net'] ?? 0, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pay Modal -->
                        <div class="modal fade" id="payModal{{ $user->id }}" tabindex="-1" aria-labelledby="payModalLabel{{ $user->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('superadmin.payments.pay', ['user' => $user->id, 'date' => $date->format('Y-m-d')]) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="payModalLabel{{ $user->id }}">
                                                <i class="bi bi-credit-card me-2"></i> Record Payment for {{ $user->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="paid_amount{{ $user->id }}" class="form-label fw-medium">
                                                    <i class="bi bi-cash me-1 text-success"></i> Amount to Pay
                                                </label>
                                                <input type="number" class="form-control" id="paid_amount{{ $user->id }}" name="paid_amount" step="0.01" min="0" max="{{ $user->unpaid_group['net'] ?? 0 }}" value="{{ $user->unpaid_group['net'] ?? 0 }}" required>
                                                <div class="form-text">Unpaid Net Total: Rp {{ number_format($user->unpaid_group['net'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="notes{{ $user->id }}" class="form-label fw-medium">
                                                    <i class="bi bi-pencil me-1"></i> Payment Notes
                                                </label>
                                                <textarea class="form-control" id="notes{{ $user->id }}" name="notes" rows="3" placeholder="Transfer details, reference number, etc..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="bi bi-x me-1"></i> Close
                                            </button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle me-1"></i> Record Payment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-3">No transactions found for @tz($date, 'd F Y')</p>
            </div>
        </div>
    @endforelse

    <!-- Transaction Summary -->
    <div class="alert alert-info mt-4">
        <h6><i class="bi bi-info-circle me-2"></i>Today's Transaction Summary</h6>
        <div class="row">
            <div class="col-md-3">
                <strong>Users with Transactions:</strong> {{ $usersWithTransactions->count() }}
            </div>
            <div class="col-md-3">
                <strong>Total Omset:</strong> Rp {{ number_format($stats['total_omset'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="col-md-3">
                <strong>Total Fees:</strong> Rp {{ number_format($stats['total_fee'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="col-md-3">
                <strong>Net Amount:</strong> Rp {{ number_format($stats['total_net'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when date changes for better UX
        const dateInput = document.getElementById('date');
        if (dateInput) {
            dateInput.addEventListener('change', function() { this.form.submit(); });
        }
    });
</script>
@endpush