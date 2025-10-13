@extends('layouts.admin')

@section('title', 'SuperAdmin Dashboard')
@section('page-title', 'SuperAdmin Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['total_users']) }}</h4>
                            <p class="mb-0">Total Users</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-2"></i>
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
                            <h4>{{ number_format($stats['total_transactions']) }}</h4>
                            <p class="mb-0">Total Transactions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-receipt fs-2"></i>
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
                            <h4>{{ number_format($stats['today_transactions']) }}</h4>
                            <p class="mb-0">Today's Transactions</p>
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
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_id }}</td>
                                        <td>{{ $transaction->user->name }}</td>
                                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'success' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ \App\Support\Tz::format($transaction->created_at, 'd/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">
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

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>
                            Register New User
                        </a>
                        <a href="{{ route('superadmin.reports') }}" class="btn btn-success">
                            <i class="bi bi-graph-up me-2"></i>
                            View Reports
                        </a>
                        <a href="{{ route('superadmin.payments') }}" class="btn btn-warning text-white">
                            <i class="bi bi-credit-card me-2"></i>
                            Manage Payments
                            @if($stats['pending_payments'] > 0)
                                <span class="badge bg-danger">{{ $stats['pending_payments'] }}</span>
                            @endif
                        </a>
                        <form action="{{ route('superadmin.backup') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-download me-2"></i>
                                Create Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Info</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <div>Laravel Version: {{ app()->version() }}</div>
                        <div>PHP Version: {{ phpversion() }}</div>
                        <div>Server Time: {{ \App\Support\Tz::format(now(), 'd/m/Y H:i:s') }} WIB</div>
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection