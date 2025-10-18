@extends('layouts.admin')

@section('title', 'Laporan Saya')
@section('page-title', 'Laporan Transaksi')

@section('content')
    <!-- Penyaring Tanggal -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.reports') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label">Pilih Tanggal</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>
                        Filter
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <small class="text-muted">Menampilkan data untuk: @tz(\Carbon\Carbon::parse($date), 'd F Y')</small>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Harian -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $daily_stats['total_transactions'] }}</h4>
                    <p class="mb-0">Total Transaksi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $daily_stats['successful_transactions'] }}</h4>
                    <p class="mb-0">Berhasil</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body text-center">
                    <h4>Rp {{ number_format($daily_stats['total_amount'], 0, ',', '.') }}</h4>
                    <p class="mb-0">Total Pendapatan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body text-center">
                    <h4>Rp {{ number_format($daily_stats['total_fee'], 0, ',', '.') }}</h4>
                    <p class="mb-0">Total Biaya</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Tren Mingguan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Tren Transaksi Mingguan</h5>
        </div>
        <div class="card-body">
            <div style="height: 350px;"><!-- Kontainer tinggi yang meningkat -->
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detail Transaksi -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Detail Transaksi</h5>
            @if($transactions->isNotEmpty())
                <span class="badge bg-info">{{ $transactions->count() }} transaksi</span>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>ID Pesanan</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Biaya</th>
                            <th>Bersih</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <div>
                                        <strong>@tz($transaction->created_at, 'H:i:s')</strong>
                                        <br>
                                        <small class="text-muted">@tz($transaction->created_at, 'd/m/Y')</small>
                                    </div>
                                </td>
                                <td>
                                    <code>{{ $transaction->order ? $transaction->order->order_id : 'N/A' }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span>
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($transaction->fee_amount > 0)
                                        <span class="text-warning">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-success">Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($transaction->status === 'success')
                                        <span class="badge bg-success">Berhasil</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="badge bg-warning">Tertunda</span>
                                    @elseif($transaction->status === 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-earmark-text fs-1"></i>
                                        <p class="mt-2">Tidak ada transaksi yang ditemukan untuk @tz(\Carbon\Carbon::parse($date), 'd F Y')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->isNotEmpty())
                <!-- Pagination jika diperlukan -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Menampilkan {{ $transactions->count() }} dari {{ $transactions->count() }} transaksi
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Submit form otomatis ketika tanggal berubah
    document.getElementById('date').addEventListener('change', function() {
        this.form.submit();
    });

    // Grafik Mingguan
    const weeklyData = @json($weekly_data);
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklyData.map(d => `${d.day} ${d.date}`), // Tambahkan hari dari minggu ke label
            datasets: [
                {
                    label: 'Total Jumlah',
                    data: weeklyData.map(d => d.total_amount),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true,
                    pointBackgroundColor: 'rgb(75, 192, 192)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: weeklyData.map(d => d.count),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true,
                    pointBackgroundColor: 'rgb(255, 99, 132)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    title: {
                        display: true,
                        text: '7 Hari Terakhir',
                        color: '#666',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    title: {
                        display: true,
                        text: 'Jumlah (Rp)',
                        color: 'rgb(75, 192, 192)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                        color: 'rgba(0,0,0,0.05)'
                    },
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi',
                        color: 'rgb(255, 99, 132)',
                        font: {
                            weight: 'bold'
                        }
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#333',
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 5,
                    displayColors: true,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y') {
                                label += 'Rp ' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y + ' transaksi';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush