@extends('layouts.admin')

@section('title', 'User Dashboard')
@section('page-title', 'User Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['today_transactions']) }}</h4>
                            <p class="mb-0">Today's Transactions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-receipt fs-2"></i>
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
                            <h4>Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Today's Revenue</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-currency-dollar fs-2"></i>
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
                            <h4>Rp {{ number_format($stats['month_revenue'], 0, ',', '.') }}</h4>
                            <p class="mb-0">This Month</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-graph-up fs-2"></i>
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
                            <h4>Rp {{ number_format($stats['pending_payment'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Pending Payment</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Order</th>
                                    <th>Amount</th>
                                    <th>Net Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_transactions as $transaction)
                                    <tr>
                                        <td><small>{{ $transaction->transaction_id }}</small></td>
                                        <td>{{ $transaction->order->order_number ?? 'N/A' }}</td>
                                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'success' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">
                                            No transactions found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Today's Payment Status</h5>
                </div>
                <div class="card-body">
                    @if($today_payment)
                        <div class="mb-3">
                            <strong>Payment Date:</strong> {{ $today_payment->payment_date->format('d/m/Y') }}
                        </div>
                        <div class="mb-3">
                            <strong>Total Omset:</strong><br>
                            <span class="fs-5 text-primary">Rp {{ number_format($today_payment->total_omset, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Fee Deducted:</strong><br>
                            <span class="text-danger">Rp {{ number_format($today_payment->total_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Net Amount:</strong><br>
                            <span class="fs-5 text-success">Rp {{ number_format($today_payment->net_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $today_payment->status === 'paid' ? 'success' : ($today_payment->status === 'partial' ? 'warning' : 'danger') }}">
                                {{ ucfirst($today_payment->status) }}
                            </span>
                        </div>
                        @if($today_payment->paid_amount > 0)
                            <div class="mb-3">
                                <strong>Paid Amount:</strong><br>
                                <span class="text-success">Rp {{ number_format($today_payment->paid_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-info-circle fs-1"></i>
                            <p>No payment record for today</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.reports') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>
                            View Reports
                        </a>
                        <a href="{{ route('user.payments') }}" class="btn btn-outline-success">
                            <i class="bi bi-credit-card me-2"></i>
                            Payment History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection