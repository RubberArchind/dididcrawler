# Transaction Fee Calculation - Implementation Summary

## Overview

The MQTT transaction listener now calculates fees based on configurable database settings instead of using hardcoded values or payload data.

## Changes Made

### 1. Updated `ListenForMqttTransactions` Command

**File:** `app/Console/Commands/ListenForMqttTransactions.php`

- ✅ Added `calculateFee()` method that calls `Setting::calculateTransactionFee()`
- ✅ Modified transaction creation to calculate fee before saving
- ✅ Enhanced output to show fee breakdown (Amount, Fee, Net Amount)
- ✅ Uses consistent fee calculation across the application

```php
// Calculate fee based on settings
$amount = $payload['amount'] ?? $order->amount;
$feeAmount = $this->calculateFee($amount);
$netAmount = $amount - $feeAmount;
```

### 2. Added Fee Settings UI

**File:** `resources/views/superadmin/settings.blade.php`

Added new "Transaction Fee Settings" card with:
- ✅ Fee Percentage input (0-100%)
- ✅ Minimum Fee input (Rp)
- ✅ Maximum Fee input (Rp)
- ✅ Real-time fee calculation example
- ✅ Live preview showing fee calculation for Rp 100,000

### 3. Fee Calculation Logic

**Existing in:** `app/Models/Setting.php`

The fee calculation follows this logic:

```
1. Calculate: fee = (amount × fee_percentage) / 100
2. Apply constraints:
   - If fee < minimum_fee → use minimum_fee
   - If fee > maximum_fee → use maximum_fee
   - Otherwise → use calculated fee
```

## Default Fee Settings

From migration: `database/migrations/2025_10_04_000000_add_fee_settings.php`

| Setting | Default Value | Type | Description |
|---------|---------------|------|-------------|
| `fee_percentage` | 2.5% | decimal | Percentage of transaction amount |
| `minimum_fee` | Rp 1,000 | integer | Minimum fee per transaction |
| `maximum_fee` | Rp 10,000 | integer | Maximum fee per transaction |

## Fee Calculation Examples

### Example 1: Small Transaction
- **Amount:** Rp 10,000
- **Calculated (2.5%):** Rp 250
- **Applied:** Rp 1,000 (minimum fee)
- **Net Amount:** Rp 9,000

### Example 2: Medium Transaction
- **Amount:** Rp 100,000
- **Calculated (2.5%):** Rp 2,500
- **Applied:** Rp 2,500 (within range)
- **Net Amount:** Rp 97,500

### Example 3: Large Transaction
- **Amount:** Rp 1,000,000
- **Calculated (2.5%):** Rp 25,000
- **Applied:** Rp 10,000 (maximum fee)
- **Net Amount:** Rp 990,000

## Usage

### 1. Configure Fee Settings

Navigate to: **SuperAdmin → Settings → Transaction Fee Settings**

Update the values:
1. Set your desired fee percentage (e.g., 2.5%)
2. Set minimum fee threshold (e.g., Rp 1,000)
3. Set maximum fee cap (e.g., Rp 10,000)
4. Click "Update Fee Settings"

### 2. Test the Calculation

The settings page shows a live example using Rp 100,000 as the test amount. Adjust the values to see how fees are calculated in real-time.

### 3. Run the Listener

```bash
php artisan mqtt:listen-transactions
```

When a payment is received with `status: "sukses"`, the listener will:

1. Get the transaction amount
2. Calculate fee using database settings
3. Calculate net amount (amount - fee)
4. Create transaction record
5. Display breakdown in console:

```
✓ Transaction created successfully: MQTT-order-TEST-001-1728550215
  Order: order-TEST-001
  Amount: Rp 100.000
  Fee: Rp 2.500
  Net Amount: Rp 97.500
  Status: success
```

## Database Schema

Fee settings are stored in the `settings` table:

```sql
SELECT * FROM settings WHERE `key` IN ('fee_percentage', 'minimum_fee', 'maximum_fee');
```

| key | value | type | description |
|-----|-------|------|-------------|
| fee_percentage | 2.5 | decimal | Global fee percentage for transactions |
| minimum_fee | 1000 | integer | Minimum fee amount per transaction |
| maximum_fee | 10000 | integer | Maximum fee amount per transaction |

## Integration Points

The fee calculation is used in multiple places:

1. **MQTT Transaction Listener** (`ListenForMqttTransactions`)
   - Calculates fees for MQTT-triggered payments

2. **Webhook Handler** (`SuperAdminController::webhook`)
   - Calculates fees for payment gateway webhooks

3. **Setting Model** (`Setting::calculateTransactionFee()`)
   - Central fee calculation method used by all services

## Testing

### Manual Test via MQTT

1. Publish message to `transaksi/status/test-order-001`:
```json
{
  "order_id": "test-order-001",
  "status": "sukses",
  "amount": 100000
}
```

2. Check console output for fee calculation
3. Verify in database:
```sql
SELECT 
    transaction_id,
    amount,
    fee_amount,
    net_amount,
    amount - fee_amount as calculated_net
FROM transactions
WHERE order_id = (SELECT id FROM orders WHERE order_id = 'test-order-001')
ORDER BY created_at DESC
LIMIT 1;
```

### Update Fee Settings Test

1. Go to Settings page
2. Change fee percentage to 5%
3. Change minimum to Rp 2,000
4. Save settings
5. Trigger new transaction
6. Verify new fee is applied

## Migration

If you haven't run the fee settings migration yet:

```bash
php artisan migrate
```

This will create the fee settings with default values.

## Troubleshooting

### Issue: Fee is always the minimum

**Cause:** Transaction amounts are too small for percentage calculation.

**Solution:** 
- Lower the minimum_fee setting, or
- Increase transaction amounts, or
- Adjust fee_percentage upward

### Issue: Fee is always the maximum

**Cause:** Transaction amounts are very large and calculated fee exceeds cap.

**Solution:**
- Increase maximum_fee setting, or
- Lower fee_percentage

### Issue: Settings not updating

**Cause:** Cache may be holding old values.

**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
```

## Security Considerations

✅ Fee settings are admin-only (SuperAdmin access required)
✅ Validation ensures values are numeric and non-negative
✅ Settings are applied consistently across all payment channels
✅ Audit trail in Laravel logs for all transactions

## Future Enhancements

Potential improvements:

- [ ] Tiered fee structure based on transaction volume
- [ ] Different fees per payment method (QRIS, VA, etc.)
- [ ] User-specific fee overrides
- [ ] Fee caps per day/month
- [ ] Promotional periods with reduced fees
- [ ] Fee reporting dashboard

## Related Files

```
app/
├── Console/Commands/
│   └── ListenForMqttTransactions.php    # MQTT listener (updated)
├── Http/Controllers/
│   └── SuperAdminController.php         # Webhook handler (uses fee calc)
└── Models/
    └── Setting.php                      # Fee calculation logic

database/migrations/
└── 2025_10_04_000000_add_fee_settings.php    # Fee settings migration

resources/views/superadmin/
└── settings.blade.php                   # Fee settings UI (updated)
```

## Support

For issues or questions about fee calculation:
1. Check `storage/logs/laravel.log` for transaction details
2. Verify settings in database: `SELECT * FROM settings WHERE key LIKE 'fee%'`
3. Test calculation in Tinker: `Setting::calculateTransactionFee(100000)`
