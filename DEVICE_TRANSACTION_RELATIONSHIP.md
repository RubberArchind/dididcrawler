# Device-Transaction Relationship

## Overview

Transactions are now linked to devices through the `device_id` foreign key. This relationship is automatically established by extracting the device UID from the order ID format.

## Order ID Format

The system expects order IDs to follow this format:

```
order-{device_uid}-{random_number}
```

**Examples:**
- `order-TEST123-608975`
- `order-DEVICE456-123456`
- `order-ESP32ABC-999888`

Where:
- `order` = Fixed prefix
- `TEST123` = Device UID (extracted and used to find device)
- `608975` = Random number for uniqueness

## Database Schema

### Migration

**File:** `database/migrations/2025_10_10_025855_add_device_id_to_transactions_table.php`

```php
Schema::table('transactions', function (Blueprint $table) {
    $table->foreignId('device_id')
          ->nullable()
          ->after('order_id')
          ->constrained()
          ->onDelete('cascade');
    $table->index('device_id');
});
```

### Transactions Table Structure

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint | No | Primary key |
| user_id | bigint | No | Foreign key to users |
| order_id | bigint | No | Foreign key to orders |
| **device_id** | **bigint** | **Yes** | **Foreign key to devices** |
| transaction_id | string | No | Unique transaction identifier |
| amount | decimal(15,2) | No | Transaction amount |
| fee_amount | decimal(15,2) | No | Fee charged |
| net_amount | decimal(15,2) | No | Amount after fee |
| status | enum | No | pending/success/failed/cancelled |
| payment_method | string | Yes | Payment method used |
| webhook_data | json | Yes | Full webhook payload |
| paid_at | timestamp | Yes | Payment timestamp |

## Model Changes

### Transaction Model

**File:** `app/Models/Transaction.php`

#### New Relationship

```php
public function device()
{
    return $this->belongsTo(Device::class);
}
```

#### Device UID Extraction Method

```php
public static function extractDeviceUidFromOrderId(string $orderId): ?string
{
    // Format: order-TEST123-608975
    $parts = explode('-', $orderId);
    
    if (count($parts) >= 3 && $parts[0] === 'order') {
        return $parts[1]; // Return TEST123
    }
    
    return null;
}
```

#### Usage Example

```php
$orderId = 'order-TEST123-608975';
$deviceUid = Transaction::extractDeviceUidFromOrderId($orderId);
// Returns: 'TEST123'

$transaction = Transaction::find(1);
$device = $transaction->device; // Access related device
echo $device->device_uid; // TEST123
```

### Device Model

**File:** `app/Models/Device.php`

#### New Relationship

```php
public function transactions(): HasMany
{
    return $this->hasMany(Transaction::class);
}
```

#### Usage Example

```php
$device = Device::where('device_uid', 'TEST123')->first();
$transactions = $device->transactions; // Get all transactions for this device

// Get total revenue for device
$totalRevenue = $device->transactions()
    ->where('status', 'success')
    ->sum('net_amount');

// Get transaction count
$transactionCount = $device->transactions()->count();
```

## MQTT Listener Integration

**File:** `app/Console/Commands/ListenForMqttTransactions.php`

The MQTT transaction listener automatically extracts the device UID from the order ID and links the transaction:

```php
// Extract device_uid from order_id and find device
$deviceUid = Transaction::extractDeviceUidFromOrderId($orderId);
$device = null;

if ($deviceUid) {
    $device = \App\Models\Device::where('device_uid', $deviceUid)->first();
    
    if ($device) {
        $this->line("Device found: {$deviceUid}");
    } else {
        $this->warn("Device not found: {$deviceUid}");
        Log::warning('Device not found for transaction', [
            'device_uid' => $deviceUid,
            'order_id' => $orderId
        ]);
    }
}

// Create the transaction with device_id
$transaction = Transaction::create([
    'user_id' => $order->user_id,
    'order_id' => $order->id,
    'device_id' => $device?->id, // Linked here
    'transaction_id' => $payload['transaction_id'] ?? 'MQTT-' . $orderId . '-' . time(),
    'amount' => $amount,
    'fee_amount' => $feeAmount,
    'net_amount' => $netAmount,
    'status' => 'success',
    'payment_method' => $payload['payment_method'] ?? 'mqtt',
    'webhook_data' => $payload,
    'paid_at' => $this->resolveTimestamp($payload) ?? now(),
]);
```

### Console Output

When a transaction is created, the listener now shows the device information:

```
✓ Transaction created successfully: MQTT-order-TEST123-608975-1728550215
  Order: order-TEST123-608975
  Device: TEST123
  Amount: Rp 100.000
  Fee: Rp 2.500
  Net Amount: Rp 97.500
  Status: success
```

## Webhook Integration

**File:** `app/Http/Controllers/SuperAdminController.php`

The webhook handler also extracts and links devices:

```php
// Extract device_uid from order_id and find device
$deviceUid = Transaction::extractDeviceUidFromOrderId($orderId);
$device = null;

if ($deviceUid) {
    $device = Device::where('device_uid', $deviceUid)->first();
    
    if (!$device) {
        Log::warning('Device not found for webhook transaction', [
            'device_uid' => $deviceUid,
            'order_id' => $orderId
        ]);
    }
}

// Create or update transaction with device_id
$transaction = Transaction::updateOrCreate(
    ['transaction_id' => $transactionId],
    [
        'user_id' => $order->user_id,
        'order_id' => $order->id,
        'device_id' => $device?->id,
        // ... other fields
    ]
);
```

## Use Cases

### 1. Device Revenue Tracking

```php
// Get device with revenue statistics
$device = Device::with(['transactions' => function($query) {
    $query->where('status', 'success');
}])->find(1);

$totalRevenue = $device->transactions->sum('net_amount');
$totalTransactions = $device->transactions->count();
$averageTransaction = $device->transactions->avg('amount');

echo "Device: {$device->device_uid}\n";
echo "Total Revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . "\n";
echo "Transactions: {$totalTransactions}\n";
echo "Average: Rp " . number_format($averageTransaction, 0, ',', '.') . "\n";
```

### 2. User's Devices Transaction Summary

```php
$user = User::with('devices.transactions')->find(1);

foreach ($user->devices as $device) {
    $revenue = $device->transactions()
        ->where('status', 'success')
        ->sum('net_amount');
    
    echo "{$device->device_uid}: Rp " . number_format($revenue, 0, ',', '.') . "\n";
}
```

### 3. Top Performing Devices

```php
$topDevices = Device::withCount(['transactions as success_count' => function($query) {
        $query->where('status', 'success');
    }])
    ->with(['transactions' => function($query) {
        $query->where('status', 'success');
    }])
    ->having('success_count', '>', 0)
    ->orderByDesc('success_count')
    ->take(10)
    ->get()
    ->map(function($device) {
        return [
            'device_uid' => $device->device_uid,
            'transaction_count' => $device->success_count,
            'total_revenue' => $device->transactions->sum('net_amount'),
        ];
    });
```

### 4. Daily Device Revenue Report

```php
$device = Device::where('device_uid', 'TEST123')->first();

$dailyRevenue = $device->transactions()
    ->where('status', 'success')
    ->whereDate('paid_at', today())
    ->selectRaw('
        COUNT(*) as transaction_count,
        SUM(amount) as gross_amount,
        SUM(fee_amount) as total_fees,
        SUM(net_amount) as net_revenue
    ')
    ->first();

echo "Daily Report for {$device->device_uid}\n";
echo "Transactions: {$dailyRevenue->transaction_count}\n";
echo "Gross: Rp " . number_format($dailyRevenue->gross_amount, 0, ',', '.') . "\n";
echo "Fees: Rp " . number_format($dailyRevenue->total_fees, 0, ',', '.') . "\n";
echo "Net: Rp " . number_format($dailyRevenue->net_revenue, 0, ',', '.') . "\n";
```

### 5. Transaction History for Device

```php
$device = Device::where('device_uid', 'TEST123')->first();

$recentTransactions = $device->transactions()
    ->with('order')
    ->orderByDesc('paid_at')
    ->limit(10)
    ->get();

foreach ($recentTransactions as $transaction) {
    echo sprintf(
        "%s | %s | Rp %s | %s\n",
        $transaction->paid_at->format('Y-m-d H:i'),
        $transaction->transaction_id,
        number_format($transaction->net_amount, 0, ',', '.'),
        $transaction->status
    );
}
```

## Testing

### Test Device UID Extraction

```bash
php artisan tinker
```

```php
$testCases = [
    'order-TEST123-608975',      // Valid
    'order-DEVICE456-123456',    // Valid
    'order-ABC-999',             // Valid
    'invalid-format',            // Invalid - returns null
    'order-only-one-dash',       // Invalid - returns null
];

foreach ($testCases as $orderId) {
    $deviceUid = \App\Models\Transaction::extractDeviceUidFromOrderId($orderId);
    echo "Order ID: {$orderId} => Device UID: " . ($deviceUid ?? 'NULL') . "\n";
}
```

### Test Transaction with Device

```bash
php artisan tinker
```

```php
// Create a test device
$device = Device::create([
    'device_uid' => 'TEST123',
    'user_id' => 1,
    'status' => 'active',
]);

// Create a test order
$order = Order::create([
    'user_id' => 1,
    'order_id' => 'order-TEST123-' . rand(100000, 999999),
    'amount' => 100000,
    'status' => 'pending',
]);

// Create a transaction
$transaction = Transaction::create([
    'user_id' => 1,
    'order_id' => $order->id,
    'device_id' => $device->id,
    'transaction_id' => 'TEST-' . time(),
    'amount' => 100000,
    'fee_amount' => 2500,
    'net_amount' => 97500,
    'status' => 'success',
    'paid_at' => now(),
]);

// Verify relationship
echo "Transaction Device: " . $transaction->device->device_uid . "\n";
echo "Device Transactions: " . $device->transactions()->count() . "\n";
```

## Migration Commands

```bash
# Run the migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

## Logging

The system logs warnings when a device cannot be found:

```
[2025-10-10 10:00:15] local.WARNING: Device not found for transaction {"device_uid":"UNKNOWN123","order_id":"order-UNKNOWN123-608975"}
```

This helps identify:
- Invalid device UIDs in order IDs
- Devices that haven't been registered yet
- Typos in device identifiers

## Backward Compatibility

- ✅ `device_id` is **nullable** - old transactions without devices still work
- ✅ System automatically attempts to link devices when possible
- ✅ Missing devices are logged but don't prevent transaction creation
- ✅ Existing transactions can be updated to add device links via migration

## Future Enhancements

Potential improvements:

- [ ] Dashboard widget showing device revenue rankings
- [ ] Device-specific fee structures
- [ ] Automatic device commission calculations
- [ ] Device performance analytics
- [ ] Transaction notifications to device owners
- [ ] Device-based revenue sharing reports

## Related Files

```
app/
├── Models/
│   ├── Transaction.php          # Device relationship + extraction method
│   └── Device.php                # Transactions relationship
├── Console/Commands/
│   └── ListenForMqttTransactions.php  # Auto-links devices
└── Http/Controllers/
    └── SuperAdminController.php  # Webhook device linking

database/migrations/
└── 2025_10_10_025855_add_device_id_to_transactions_table.php
```

## Support

For questions about device-transaction relationships:
1. Check transaction logs: `tail -f storage/logs/laravel.log`
2. Verify device exists: `Device::where('device_uid', 'TEST123')->exists()`
3. Test extraction: `Transaction::extractDeviceUidFromOrderId($orderId)`
4. Check device transactions: `$device->transactions()->count()`
