@extends('layouts.admin')

@section('title', 'Pembayaran Saya')
@section('page-title', 'Riwayat Pembayaran')

@section('content')
    <!-- Penyaring Bulan -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.payments') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Pilih Bulan</label>
                    <input type="month" class="form-control" id="month" name="month" value="{{ $month }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>
                        Filter
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <small class="text-muted">Menampilkan data untuk: @tz(\Carbon\Carbon::parse($month), 'F Y')</small>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Bulanan -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $monthly_stats['total_payments'] }}</h4>
                    <p class="mb-0">Total Pembayaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $monthly_stats['paid_count'] }}</h4>
                    <p class="mb-0">Dibayar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body text-center">
                    <h4>Rp {{ number_format($monthly_stats['total_amount'], 0, ',', '.') }}</h4>
                    <p class="mb-0">Total Dihasilkan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <h4>Rp {{ number_format($monthly_stats['paid_amount'], 0, ',', '.') }}</h4>
                    <p class="mb-0">Jumlah Dibayar</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Rekening Pembayaran -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Informasi Rekening Pembayaran</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Nama Rekening:</strong></td>
                            <td>{{ auth()->user()->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nomor Rekening:</strong></td>
                            <td><code>{{ auth()->user()->account_number ?: 'Belum diatur' }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Bank/Penyedia:</strong></td>
                            <td>{{ auth()->user()->bank_name ?: 'Tidak ditentukan' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Telepon:</strong></td>
                            <td>{{ auth()->user()->phone_number ?: 'Belum diatur' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ auth()->user()->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Anggota Sejak:</strong></td>
                            <td>@tz(auth()->user()->created_at, 'd F Y')</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if(!auth()->user()->account_number)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Silakan perbarui nomor rekening Anda di profil untuk menerima pembayaran.
                    <a href="{{ route('profile.edit') }}" class="alert-link">Perbarui Profil</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Riwayat Pembayaran -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Riwayat Pembayaran</h5>
            @if($payments->isNotEmpty())
                <span class="badge bg-info">{{ $payments->count() }} pembayaran</span>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <th>Periode</th>
                            <th>Total Omset</th>
                            <th>Biaya Dikurangi</th>
                            <th>Jumlah Bersih</th>
                            <th>Jumlah Dibayar</th>
                            <th>Status</th>
                            <th>Catatan</th>
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
                                            Dibayar
                                        </span>
                                    @elseif($payment->status === 'partial')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>
                                            Sebagian
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-hourglass me-1"></i>
                                            Tertunda
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

                            <!-- Modal Catatan -->
                            @if($payment->notes)
                            <div class="modal fade" id="notesModal{{ $payment->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Catatan Pembayaran - @tz($payment->payment_date, 'd/m/Y')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <strong>Jumlah Pembayaran:</strong><br>
                                                    Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}
                                                </div>
                                                <div class="col-6">
                                                        <strong>Dibayar Pada:</strong><br>
                                                        {{ $payment->paid_at ? \App\Support\Tz::format($payment->paid_at, 'd/m/Y H:i:s') : '-' }}
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Catatan:</strong><br>
                                                <div class="bg-light p-3 rounded">
                                                    {{ $payment->notes }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
                                        <p class="mt-2">Tidak ada pembayaran yang ditemukan untuk @tz(\Carbon\Carbon::parse($month), 'F Y')</p>
                                        <p class="small">Pembayaran diproses setiap hari berdasarkan transaksi sukses Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->isNotEmpty())
                <!-- Ringkasan Pembayaran -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>{{ $payments->where('status', 'paid')->count() }}</h5>
                                <small class="text-success">Pembayaran Dibayar</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>{{ $payments->where('status', 'pending')->count() }}</h5>
                                <small class="text-warning">Pembayaran Tertunda</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('net_amount'), 0, ',', '.') }}</h5>
                                <small class="text-muted">Total Dihasilkan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h5>Rp {{ number_format($payments->sum('paid_amount'), 0, ',', '.') }}</h5>
                                <small class="text-success">Total Diterima</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pemberitahuan Saldo Tertunda -->
                @php
                    $outstanding = $payments->sum('net_amount') - $payments->sum('paid_amount');
                @endphp
                @if($outstanding > 0)
                    <div class="alert alert-info mt-4">
                        <h6><i class="bi bi-info-circle me-2"></i>Saldo Tertunda</h6>
                        <p class="mb-0">
                            Anda memiliki saldo tertunda sebesar <strong>Rp {{ number_format($outstanding, 0, ',', '.') }}</strong> 
                            yang akan diproses dalam siklus pembayaran berikutnya.
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Submit form otomatis ketika bulan berubah
    document.getElementById('month').addEventListener('change', function() {
        this.form.submit();
    });

    // Inisialisasi tooltip
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush