<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Transaction - DIDID Claw Machine</title>
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
            <h1>🎉 New Transaction Received!</h1>
            <div class="success-badge">TRANSACTION SUCCESSFUL</div>
        </div>

        <p>Hello <strong>{{ $user->name }}</strong>,</p>
        
        <p>Great news! You've received a new transaction from your DIDID Claw Machine.</p>

        @if($device)
        <div class="device-info">
            <strong>Device:</strong> {{ $device->device_uid ?? 'N/A' }}
            @if($device->name)
                <br><small>{{ $device->name }}</small>
            @endif
        </div>
        @endif

        <div class="transaction-details">
            <h3 style="margin-top: 0; color: #2c3e50;">Transaction Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value">{{ $transaction->transaction_id }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Order ID:</span>
                <span class="detail-value">{{ $transaction->order_id }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ ucfirst($transaction->payment_method) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value">{{ $transaction->paid_at->format('d M Y, H:i:s') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Gross Amount:</span>
                <span class="detail-value">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
            </div>
            
            @if($transaction->fee_amount > 0)
            <div class="detail-row">
                <span class="detail-label">Transaction Fee:</span>
                <span class="detail-value">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Net Amount:</span>
                <span class="detail-value amount">Rp {{ number_format($transaction->net_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <p>This amount will be included in your next payout calculation.</p>

        <div class="footer">
            <p>Thank you for using DIDID Claw Machine!</p>
            <p><small>This is an automated email. Please do not reply to this message.</small></p>
        </div>
    </div>
</body>
</html>