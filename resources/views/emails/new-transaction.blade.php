<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Baru - DIDID Claw Machine</title>
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
        .success-badge {
            background-color: #27ae60;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .transaction-details {
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
        .device-info {
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Transaksi Baru Diterima!</h1>
            <div class="success-badge">TRANSAKSI BERHASIL</div>
        </div>

        <p>Halo <strong>{{ $user->name }}</strong>,</p>
        
        <p>Berita bagus! Anda telah menerima transaksi baru dari Mesin Capit DIDID Anda.</p>

        @if($device)
        <div class="device-info">
            <strong>Perangkat:</strong> {{ $device->device_uid ?? 'N/A' }}
            @if($device->name)
                <br><small>{{ $device->name }}</small>
            @endif
        </div>
        @endif

        <div class="transaction-details">
            <h3 style="margin-top: 0; color: #2c3e50;">Detail Transaksi</h3>
            
            <div class="detail-row">
                <span class="detail-label">ID Transaksi:</span>
                <span class="detail-value">{{ $transaction->transaction_id }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">ID Pesanan:</span>
                <span class="detail-value">{{ $transaction->order_id }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Metode Pembayaran:</span>
                <span class="detail-value">{{ ucfirst($transaction->payment_method) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Tanggal & Waktu:</span>
                <span class="detail-value">@tz($transaction->paid_at, 'd M Y, H:i:s') WIB</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Jumlah Kotor:</span>
                <span class="detail-value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>
            
            @if($transaction->fee_amount > 0)
            <div class="detail-row">
                <span class="detail-label">Biaya Transaksi:</span>
                <span class="detail-value">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Jumlah Bersih:</span>
                <span class="detail-value amount">Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <p>Jumlah ini akan dimasukkan dalam perhitungan pembayaran Anda berikutnya.</p>

        <div class="footer">
            <p>Terima kasih telah menggunakan DIDID Claw Machine!</p>
            <p><small>Ini adalah email otomatis. Silakan jangan balas pesan ini.</small></p>
        </div>
    </div>
</body>
</html>