@extends('layouts.admin')

@section('title', 'Dasbor Pengguna')
@section('page-title', 'Dasbor Pengguna')

@section('content')
    <!-- Kartu Statistik -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['today_transactions']) }}</h4>
                            <p class="mb-0">Transaksi Hari Ini</p>
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
                            <p class="mb-0">Pendapatan Hari Ini</p>
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
                            <p class="mb-0">Bulan Ini</p>
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
                            <p class="mb-0">Pembayaran Tertunda</p>
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
        <!-- Transaksi Terbaru -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transaksi Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Perangkat</th>
                                    <th>Pesanan</th>
                                    <th>Jumlah</th>
                                    <th>Jumlah Bersih</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_transactions as $transaction)
                                    <tr>
                                        <td>
                                            @if($transaction->device)
                                                <small><strong>{{ $transaction->device->device_uid }}</strong></small>
                                                @if($transaction->device->name)
                                                    <br><small class="text-muted">{{ $transaction->device->name }}</small>
                                                @endif
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->order_id ?? 'N/A' }}</td>
                                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'success' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>@tz($transaction->created_at, 'd/m/Y H:i')</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">
                                            Tidak ada transaksi yang ditemukan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Pembayaran -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Pembayaran Hari Ini</h5>
                </div>
                <div class="card-body">
                    @if($today_payment)
                        <div class="mb-3">
                            <strong>Tanggal Pembayaran:</strong> @tz($today_payment->payment_date, 'd/m/Y')
                        </div>
                        <div class="mb-3">
                            <strong>Total Omset:</strong><br>
                            <span class="fs-5 text-primary">Rp {{ number_format($today_payment->total_omset, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Biaya yang Dikurangi:</strong><br>
                            <span class="text-danger">Rp {{ number_format($today_payment->total_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Jumlah Bersih:</strong><br>
                            <span class="fs-5 text-success">Rp {{ number_format($today_payment->net_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $today_payment->status === 'paid' ? 'success' : ($today_payment->status === 'partial' ? 'warning' : 'danger') }}">
                                {{ $today_payment->status === 'paid' ? 'Dibayar' : ($today_payment->status === 'partial' ? 'Sebagian' : 'Tertunda') }}
                            </span>
                        </div>
                        @if($today_payment->paid_amount > 0)
                            <div class="mb-3">
                                <strong>Jumlah yang Dibayar:</strong><br>
                                <span class="text-success">Rp {{ number_format($today_payment->paid_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-info-circle fs-1"></i>
                            <p>Tidak ada catatan pembayaran untuk hari ini</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tautan Cepat -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tautan Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.reports') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>
                            Lihat Laporan
                        </a>
                        <a href="{{ route('user.payments') }}" class="btn btn-outline-success">
                            <i class="bi bi-credit-card me-2"></i>
                            Riwayat Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection