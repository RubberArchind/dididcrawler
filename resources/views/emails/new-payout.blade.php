<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Baru - DIDID Claw Machine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .status-badge {
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .status-paid {
            background-color: #27ae60;
        }
        .status-pending {
            background-color: #f39c12;
        }
        .payout-details {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #bdc3c7;
        }
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 16px;
        }
        .detail-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .detail-value {
            color: #34495e;
        }
        .amount {
            color: #27ae60;
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 14px;
        }
        .account-info {
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .notes {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 {{ $payment->isPaid() ? 'Pembayaran Diproses!' : 'Pembayaran Dijadwalkan!' }}</h1>
            <div class="status-badge {{ $payment->isPaid() ? 'status-paid' : 'status-pending' }}">
                {{ strtoupper($payment->status === 'paid' ? 'DIBAYAR' : ($payment->status === 'partial' ? 'SEBAGIAN' : 'TERTUNDA')) }}
            </div>
        </div>

        <p>Halo <strong>{{ $user->name }}</strong>,</p>
        
        <p>
            @if($payment->isPaid())
                Pembayaran Anda telah berhasil diproses dan dana telah dikirim ke rekening Anda.
            @else
                Pembayaran baru telah dijadwalkan untuk rekening Anda. Pembayaran sedang diproses.
            @endif
        </p>

        <div class="account-info">
            <strong>Detail Rekening:</strong><br>
            Nomor Rekening: {{ $user->account_number }}<br>
            Nama Rekening: {{ $user->name }}
        </div>

        <div class="payout-details">
            <h3 style="margin-top: 0; color: #2c3e50;">Detail Pembayaran</h3>
            
            <div class="detail-row">
                <span class="detail-label">Periode Pembayaran:</span>
                <span class="detail-value">{{ $payment->payment_date->format('d M Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total Pendapatan:</span>
                <span class="detail-value">Rp {{ number_format($payment->total_omset, 0, ',', '.') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total Biaya:</span>
                <span class="detail-value">Rp {{ number_format($payment->total_fee, 0, ',', '.') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Jumlah Bersih:</span>
                <span class="detail-value">Rp {{ number_format($payment->net_amount, 0, ',', '.') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Jumlah yang Dibayar:</span>
                <span class="detail-value amount">Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}</span>
            </div>
            
            @if($payment->paid_at)
            <div class="detail-row">
                <span class="detail-label">Tanggal Pembayaran:</span>
                <span class="detail-value">@tz($payment->paid_at, 'd M Y, H:i:s') WIB</span>
            </div>
            @endif
        </div>

        @if($payment->notes)
        <div class="notes">
            <strong>Catatan:</strong><br>
            {{ $payment->notes }}
        </div>
        @endif

        @if($payment->isPending())
        <p>
            <strong>Langkah Selanjutnya:</strong><br>
            Pembayaran Anda sedang diproses dan seharusnya tiba di rekening Anda dalam 1-3 hari kerja. 
            Anda akan menerima email konfirmasi lagi setelah pembayaran selesai.
        </p>
        @else
        <p>
            <strong>Pembayaran Selesai:</strong><br>
            Dana telah ditransfer ke rekening terdaftar Anda. Silakan periksa laporan bank Anda untuk konfirmasi.
        </p>
        @endif

        <div class="footer">
            <p>Terima kasih telah menjadi bagian dari jaringan DIDID Claw Machine!</p>
            <p><small>Ini adalah email otomatis. Silakan jangan balas pesan ini.</small></p>
        </div>
    </div>
</body>
</html>