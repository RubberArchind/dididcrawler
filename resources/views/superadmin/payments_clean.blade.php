@extends('layouts.admin')

@section('title', 'Payment Management')

@section('page-title', 'Daily Payment Management')

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
                            <h4 class="mb-0">Rp {{ number_format($stats['total_fees'], 0, ',', '.') }}</h4>
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
                                <strong class="text-warning">Rp {{ number_format($user->total_fees, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Net Amount</small>
                                <strong class="text-success">Rp {{ number_format($user->net_amount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Paid Transactions -->
                @if($user->paid_transactions->count() > 0)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <span class="badge bg-success me-2">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Paid Transactions
                                </span>
                                <small class="text-muted">({{ $user->paid_transactions->count() }} transactions)</small>
                            </h6>
                            <div class="text-end">
                                <small class="text-muted d-block">Paid Net Total</small>
                                <strong class="text-success">Rp {{ number_format($user->paid_net, 0, ',', '.') }}</strong>
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
                                    @foreach($user->paid_transactions as $transaction)
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
                                        <th class="text-end">Rp {{ number_format($user->paid_transactions->sum('amount'), 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->paid_transactions->sum('fee_amount'), 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->paid_net, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Unpaid Transactions -->
                @if($user->unpaid_transactions->count() > 0)
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <span class="badge bg-danger me-2">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Unpaid Transactions
                                </span>
                                <small class="text-muted">({{ $user->unpaid_transactions->count() }} transactions)</small>
                            </h6>
                            <div class="text-end">
                                <small class="text-muted d-block">Unpaid Net Total</small>
                                <strong class="text-danger">Rp {{ number_format($user->unpaid_net, 0, ',', '.') }}</strong>
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
                                    @foreach($user->unpaid_transactions as $transaction)
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
                                        <th class="text-end">Rp {{ number_format($user->unpaid_transactions->sum('amount'), 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->unpaid_transactions->sum('fee_amount'), 0, ',', '.') }}</th>
                                        <th class="text-end">Rp {{ number_format($user->unpaid_net, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
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
@endsection
