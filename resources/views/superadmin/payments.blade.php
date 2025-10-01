@extends('layouts.admin')

@section('title', 'Payment Management')
@section('page-title', 'Daily Payment Management')

@section('content')
    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.payments') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
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

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daily Payments</h5>
            @if($payments->isNotEmpty())
                <span class="badge bg-info">{{ $payments->count() }} users</span>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
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
                                        <small class="text-muted">Acc: {{ $payment->user->account_number }}</small>
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
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($payment->status === 'partial')
                                        <span class="badge bg-warning">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->status !== 'paid')
                                        <button type="button" class="btn btn-sm btn-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#paymentModal{{ $payment->id }}">
                                            <i class="bi bi-credit-card me-1"></i>
                                            Pay
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#paymentModal{{ $payment->id }}">
                                            <i class="bi bi-eye me-1"></i>
                                            View
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Payment Modal -->
                            <div class="modal fade" id="paymentModal{{ $payment->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                {{ $payment->status === 'paid' ? 'Payment Details' : 'Record Payment' }} - {{ $payment->user->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('superadmin.payments.pay', $payment) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <strong>Payment Date:</strong><br>
                                                        {{ $payment->payment_date->format('d/m/Y') }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Account Number:</strong><br>
                                                        {{ $payment->user->account_number }}
                                                    </div>
                                                </div>
                                                <hr>
                                                
                                                <div class="row">
                                                    <div class="col-12">
                                                        <table class="table table-sm">
                                                            <tr>
                                                                <td><strong>Total Omset:</strong></td>
                                                                <td class="text-end">Rp {{ number_format($payment->total_omset, 0, ',', '.') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Fee Deducted:</strong></td>
                                                                <td class="text-end text-warning">- Rp {{ number_format($payment->total_fee, 0, ',', '.') }}</td>
                                                            </tr>
                                                            <tr class="table-success">
                                                                <td><strong>Net Amount:</strong></td>
                                                                <td class="text-end"><strong>Rp {{ number_format($payment->net_amount, 0, ',', '.') }}</strong></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                @if($payment->status !== 'paid')
                                                    <div class="mb-3">
                                                        <label for="paid_amount{{ $payment->id }}" class="form-label">Amount to Pay</label>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="paid_amount{{ $payment->id }}" 
                                                               name="paid_amount" 
                                                               step="0.01" 
                                                               max="{{ $payment->net_amount }}"
                                                               value="{{ $payment->net_amount - $payment->paid_amount }}"
                                                               required>
                                                        <small class="form-text text-muted">
                                                            Remaining: Rp {{ number_format($payment->net_amount - $payment->paid_amount, 0, ',', '.') }}
                                                        </small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="notes{{ $payment->id }}" class="form-label">Payment Notes</label>
                                                        <textarea class="form-control" 
                                                                  id="notes{{ $payment->id }}" 
                                                                  name="notes" 
                                                                  rows="3" 
                                                                  placeholder="Transfer details, reference number, etc...">{{ $payment->notes }}</textarea>
                                                    </div>
                                                @else
                                                    <div class="alert alert-success">
                                                        <strong>Payment Completed!</strong><br>
                                                        Paid: Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}<br>
                                                        Date: {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : '-' }}<br>
                                                        @if($payment->notes)
                                                            Notes: {{ $payment->notes }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                @if($payment->status !== 'paid')
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check-circle me-2"></i>
                                                        Record Payment
                                                    </button>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-credit-card fs-1"></i>
                                        <p class="mt-2">No payments found for {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
                                        <p class="small">Payments are automatically generated when there are successful transactions for the day.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->isNotEmpty())
                <!-- Summary -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('total_omset'), 0, ',', '.') }}</h5>
                                <small class="text-muted">Total Omset</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('total_fee'), 0, ',', '.') }}</h5>
                                <small>Total Fees</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('net_amount'), 0, ',', '.') }}</h5>
                                <small>Net Amount</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('paid_amount'), 0, ',', '.') }}</h5>
                                <small>Total Paid</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when date changes
    document.getElementById('date').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush