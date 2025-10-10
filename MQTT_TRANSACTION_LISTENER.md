# MQTT Transaction Status Listener

## Overview

The `mqtt:listen-transactions` command listens for payment status updates via MQTT and automatically creates transaction records when payments are confirmed.

## Usage

### Basic Usage

```bash
php artisan mqtt:listen-transactions
```

### With Custom Options

```bash
php artisan mqtt:listen-transactions \
  --host=broker.example.com \
  --port=8883 \
  --username=user \
  --password=pass \
  --topic="transaksi/status/#"
```

## Topic Format

The listener subscribes to: `transaksi/status/#` (wildcard for all order IDs)

Individual messages are published to: `transaksi/status/<order_id>`

Example: `transaksi/status/order-TEST123-608975`

## Message Format

### JSON Format (Default)

```json
{
  "order_id": "order-TEST123-608975",
  "status": "paid",
  "amount": 50000,
  "fee_amount": 1500,
  "payment_method": "qris",
  "transaction_id": "TRX-123456",
  "paid_at": "2025-10-10T08:30:00Z"
}
```

### MessagePack Format (Binary)

The listener also supports MessagePack binary encoding for reduced bandwidth:

```arduino
// ESP32 / Arduino Example
#include <ArduinoJson.h>
#include <PubSubClient.h>

StaticJsonDocument<256> doc;
doc["order_id"] = "order-TEST123-608975";
doc["status"] = "paid";
doc["amount"] = 50000;
doc["fee_amount"] = 1500;

uint8_t buffer[128];
size_t len = serializeMsgPack(doc, buffer);
mqtt.publish("transaksi/status/order-TEST123-608975", buffer, len);
```

## Required Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `order_id` | string | Yes | Order identifier (from topic or payload) |
| `status` | string | Yes | Payment status (must be "paid" to process) |
| `amount` | decimal | No | Transaction amount (defaults to order amount) |
| `fee_amount` | decimal | No | Payment gateway fees (defaults to 0) |
| `payment_method` | string | No | Payment method used |
| `transaction_id` | string | No | External transaction ID (auto-generated if missing) |
| `paid_at` | timestamp | No | Payment timestamp (defaults to now) |

## Behavior

### Status Processing

- ✅ **`status: "paid"`** - Creates transaction and marks order as completed
- ⏭️ **`status: "pending"`** - Skipped (not processed)
- ⏭️ **`status: "failed"`** - Skipped (not processed)
- ⏭️ **`status: "cancelled"`** - Skipped (not processed)

### Transaction Creation

When a `paid` status is received:

1. **Find Order** - Looks up order by `order_id` or `external_order_id`
2. **Check Duplicates** - Prevents duplicate successful transactions
3. **Create Transaction** - Inserts new record with status `success`
4. **Update Order** - Sets order status to `completed`
5. **Log Activity** - Writes to Laravel logs

### Database Updates

```sql
-- Transaction Record Created
INSERT INTO transactions (
    user_id,
    order_id,
    transaction_id,
    amount,
    fee_amount,
    net_amount,
    status,
    payment_method,
    webhook_data,
    paid_at
) VALUES (...);

-- Order Status Updated
UPDATE orders SET status = 'completed' WHERE id = ?;
```

## Configuration

Uses the same MQTT configuration as the heartbeat listener:

**Via Environment Variables** (`.env`):
```env
MQTT_HOST=broker.example.com
MQTT_PORT=8883
MQTT_USERNAME=your_username
MQTT_PASSWORD=your_password
```

**Via Settings Panel**:
Navigate to SuperAdmin → Settings → MQTT Configuration

## Running in Production

### Systemd Service

Create `/etc/systemd/system/mqtt-transactions.service`:

```ini
[Unit]
Description=MQTT Transaction Status Listener
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/dididcrawler
ExecStart=/usr/bin/php /var/www/dididcrawler/artisan mqtt:listen-transactions
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable mqtt-transactions
sudo systemctl start mqtt-transactions
sudo systemctl status mqtt-transactions
```

### Docker Compose

```yaml
services:
  mqtt-transactions:
    build: .
    command: php artisan mqtt:listen-transactions
    environment:
      - MQTT_HOST=${MQTT_HOST}
      - MQTT_PORT=${MQTT_PORT}
      - MQTT_USERNAME=${MQTT_USERNAME}
      - MQTT_PASSWORD=${MQTT_PASSWORD}
    restart: unless-stopped
    depends_on:
      - app
```

### Supervisor

Create `/etc/supervisor/conf.d/mqtt-transactions.conf`:

```ini
[program:mqtt-transactions]
process_name=%(program_name)s
command=php /var/www/dididcrawler/artisan mqtt:listen-transactions
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/dididcrawler/storage/logs/mqtt-transactions.log
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mqtt-transactions
```

## Testing

### Manual Testing with MQTT Explorer

1. Connect to your MQTT broker
2. Publish to `transaksi/status/order-TEST-001`:
```json
{
  "order_id": "order-TEST-001",
  "status": "paid",
  "amount": 10000
}
```
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Verify transaction created: Check database or admin panel

### Testing with mosquitto_pub

```bash
mosquitto_pub -h broker.example.com -p 8883 \
  -u username -P password \
  -t "transaksi/status/order-TEST-001" \
  -m '{"order_id":"order-TEST-001","status":"paid","amount":10000}'
```

## Logging

All activity is logged to `storage/logs/laravel.log`:

```
[2025-10-10 08:30:15] local.INFO: Transaction created from MQTT {"transaction_id":"MQTT-order-TEST123-608975-1728550215","order_id":"order-TEST123-608975","amount":"50000.00"}
```

Errors are also logged:
```
[2025-10-10 08:30:20] local.ERROR: Order not found for transaction {"order_id":"invalid-order"}
```

## Troubleshooting

### Listener Won't Connect

1. Check MQTT credentials in settings or `.env`
2. Verify broker allows TLS connections (port 8883)
3. Check firewall allows outbound connections
4. Test connection with MQTT Explorer

### Transaction Not Created

1. Verify status is exactly `"paid"` (case-sensitive)
2. Check order exists in database
3. Ensure no duplicate transaction already exists
4. Review Laravel logs for errors

### Messages Not Received

1. Verify topic subscription: `transaksi/status/#`
2. Check message is published to correct topic
3. Ensure listener is running: `ps aux | grep mqtt`
4. Test with MQTT Explorer to see if messages arrive

## Security Considerations

- ✅ Uses TLS encryption by default
- ✅ Requires authentication (username/password)
- ✅ Validates order exists before creating transaction
- ✅ Prevents duplicate transactions
- ✅ Logs all activity for audit trail
- ⚠️ Consider rate limiting for production
- ⚠️ Use strong MQTT credentials
- ⚠️ Restrict MQTT topic access per device/user

## Related Commands

- `php artisan mqtt:listen-heartbeats` - Device heartbeat listener
- `php artisan list mqtt` - List all MQTT commands
- `php artisan tinker` - Test transaction creation manually

## Support

For issues or questions:
- Check `storage/logs/laravel.log`
- Review MQTT broker logs
- Test connectivity with MQTT Explorer
- Verify database migrations are up to date
