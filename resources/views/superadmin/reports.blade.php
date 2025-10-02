@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Daily Reports')

@section('content')
    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.reports') }}" class="row align-items-end">
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
                    <small class="text-muted">Showing data for: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</small>
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
                            <h4>{{ number_format($total_stats['total_transactions']) }}</h4>
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
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Rp {{ number_format($total_stats['total_amount'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Total Amount</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-currency-dollar fs-2"></i>
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
                            <h4>Rp {{ number_format($total_stats['total_fee'], 0, ',', '.') }}</h4>
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
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Rp {{ number_format($total_stats['net_amount'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Net Amount</p>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-graph-up fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Trend Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Weekly Transaction Trend</h5>
        </div>
        <div class="card-body">
            <div style="height: 350px;"><!-- Taller chart container -->
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Report by User -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daily Report by User</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Transactions</th>
                            <th>Total Amount</th>
                            <th>Total Fee</th>
                            <th>Net Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daily_report as $report)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $report->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $report->user->username }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small>{{ $report->user->email }}</small>
                                        <br>
                                        <small class="text-muted">{{ $report->user->phone_number }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ number_format($report->transaction_count) }}</span>
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($report->total_amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <span class="text-warning">Rp {{ number_format($report->total_fee, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <strong class="text-success">Rp {{ number_format($report->net_amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info" 
                                                onclick="viewUserTransactions({{ $report->user->id }}, '{{ $date }}')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="{{ route('superadmin.payments') }}?date={{ $date }}" 
                                           class="btn btn-outline-success">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-graph-up fs-1"></i>
                                        <p class="mt-2">No transactions found for {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
                                        <p class="small">Try selecting a different date or check if there were any successful transactions.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($daily_report->isNotEmpty())
        <!-- Export Options -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0">Export Report</h6>
                        <small class="text-muted">Download the report for {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-success" onclick="exportToCsv()">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                Export to CSV
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="exportToPdf()">
                                <i class="bi bi-file-earmark-pdf me-2"></i>
                                Export to PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function viewUserTransactions(userId, date) {
        // This would typically open a modal or navigate to detailed view
        alert(`View transactions for User ID: ${userId} on date: ${date}`);
        // You can implement a detailed transaction modal here
    }

    function exportToCsv() {
        // Implement CSV export functionality
        alert('CSV export functionality can be implemented here');
    }

    function exportToPdf() {
        // Implement PDF export functionality
        alert('PDF export functionality can be implemented here');
    }

    // Auto-submit form when date changes for better UX
    document.getElementById('date').addEventListener('change', function() {
        this.form.submit();
    });
    
    // Weekly Chart
    const weeklyData = @json($weekly_data);
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: weeklyData.map(d => `${d.day} ${d.date}`), // Add day of week to label
            datasets: [
                {
                    label: 'Total Amount',
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
                    label: 'Transaction Count',
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
                        text: 'Last 7 Days',
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
                        text: 'Amount (Rp)',
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
                        text: 'Transaction Count',
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
                                label += context.parsed.y + ' transactions';
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