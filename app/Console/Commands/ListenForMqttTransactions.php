<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MessagePack\MessagePack;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;

class ListenForMqttTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen-transactions 
                            {--host= : MQTT broker host}
                            {--port=1883 : MQTT broker port}
                            {--username= : MQTT username}
                            {--password= : MQTT password}
                            {--topic=transaksi/status/# : MQTT topic to subscribe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for MQTT transaction status updates and create transactions when status is paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $host = $this->option('host') ?? Setting::get('mqtt_host', config('mqtt.host'));
        // $port = $this->option('port') ?? Setting::get('mqtt_port', config('mqtt.port'));
        // $username = $this->option('username') ?? Setting::get('mqtt_username', config('mqtt.username'));
        // $password = $this->option('password') ?? Setting::get('mqtt_password', config('mqtt.password'));
        $host = Setting::get('mqtt_host', config('mqtt.host'));
        $port = (int) Setting::get('mqtt_port', config('mqtt.port'));
        $username = Setting::get('mqtt_username', config('mqtt.username'));
        $password = Setting::get('mqtt_password', config('mqtt.password'));
        $topic = $this->option('topic') ?? 'transaksi/status/#';

        $this->info('Starting MQTT Transaction Status Listener...');
        $this->info("Connecting to: {$host}:{$port}");
        $this->info("Username: " . ($username ?: '(none)'));
        $this->info("Subscribing to: {$topic}");

        try {
            $clientId = 'laravel_transaction_listener_' . uniqid();
            $mqtt = new MqttClient($host, $port, $clientId);

            // Don't set LastWillTopic in connection settings - it might cause ACL issues
            $connectionSettings = (new ConnectionSettings)
                ->setUseTls(false)
                ->setTlsSelfSignedAllowed(true)
                ->setUsername($username ?: null)
                ->setPassword($password ?: null)
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(10);

            $mqtt->connect($connectionSettings, true);

            $this->info('Connected successfully!');
            $this->info('Waiting for transaction status messages...');

            $mqtt->subscribe($topic, function ($topic, $message) {
                $this->processMessage($topic, $message);
            }, 0);

            $mqtt->loop(true);

        } catch (MqttClientException $e) {
            $this->error('MQTT Error: ' . $e->getMessage());
            Log::error('MQTT Transaction Listener Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }

        return 0;
    }

    protected function processMessage(string $topic, string $message): void
    {
        $this->info("Received message on topic: {$topic}");

        try {
            // Extract order_id from topic: transaksi/status/order-TEST123-608975
            $topicParts = explode('/', $topic);
            $orderIdFromTopic = end($topicParts);

            // Decode the payload (supports both JSON and MessagePack)
            $payload = $this->decodePayload($message);

            if (!$payload || !is_array($payload)) {
                $this->warn('Invalid payload format');
                Log::warning('Invalid transaction payload', ['topic' => $topic, 'message' => $message]);
                return;
            }

            $this->line('Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));

            // Extract order_id and status from payload
            $orderId = $payload['order_id'] ?? $payload['oid'] ?? $orderIdFromTopic;
            $status = $payload['status'] ?? null;

            if (!$orderId || !$status) {
                $this->warn('Missing order_id or status in payload');
                Log::warning('Missing transaction data', ['payload' => $payload]);
                return;
            }

            // Only process if status is 'sukses'
            if ($status !== 'sukses') {
                $this->line("Status is '{$status}', skipping (only 'sukses' status is processed)");
                return;
            }

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

            // Get amount from payload
            $amount = $payload['amount'] ?? null;
            
            if (!$amount) {
                $this->error("Amount not found in payload");
                Log::error('Amount missing from transaction payload', ['order_id' => $orderId, 'payload' => $payload]);
                return;
            }

            // Check if order already exists and has a successful transaction
            $existingOrder = Order::where('order_id', $orderId)
                ->orWhere('external_order_id', $orderId)
                ->first();

            if ($existingOrder) {
                // Order exists - check if it already has a successful transaction
                $existingTransaction = Transaction::where('order_id', $orderId)
                    ->where('status', 'success')
                    ->first();

                if ($existingTransaction) {
                    $this->warn("Transaction already exists for order: {$orderId}");
                    return;
                }
                
                $order = $existingOrder;
                $this->line("Using existing order");
            } else {
                // Create new order
                $order = Order::create([
                    'user_id' => $device?->user_id ?? 1, // Default to user 1 if no device
                    'order_id' => $orderId,
                    'external_order_id' => $orderId,
                    'amount' => $amount,
                    'status' => 'pending',
                    'description' => 'Order from MQTT transaction',
                ]);
                
                $this->line("New order created: {$order->id}");
            }

            // Calculate fee based on settings
            $feeAmount = $this->calculateFee($amount);
            $netAmount = $amount - $feeAmount;

            // Create the transaction
            $transaction = Transaction::create([
                'user_id' => $order->user_id,
                'order_id' => $orderId,
                'device_id' => $device?->id,
                'transaction_id' => $payload['transaction_id'] ?? 'MQTT-' . $orderId . '-' . time(),
                'amount' => $amount,
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'status' => 'success',
                'payment_method' => $payload['payment_method'] ?? 'mqtt',
                'webhook_data' => $payload,
                'paid_at' => $this->resolveTimestamp($payload) ?? now(),
            ]);

            // Update order status to completed
            $order->update(['status' => 'completed']);

            // Note: Email notification is sent by TransactionObserver on model creation
            // to avoid duplicate emails and follow the observer pattern

            $this->info("✓ Transaction created successfully: {$transaction->transaction_id}");
            $this->info("  Order: {$orderId}");
            $this->info("  Device: " . ($device ? $device->device_uid : 'N/A'));
            $this->info("  Amount: Rp " . number_format($transaction->amount, 0, ',', '.'));
            $this->info("  Fee: Rp " . number_format($transaction->fee_amount, 0, ',', '.'));
            $this->info("  Net Amount: Rp " . number_format($transaction->net_amount, 0, ',', '.'));
            $this->info("  Status: {$transaction->status}");

            Log::info('Transaction created from MQTT', [
                'transaction_id' => $transaction->transaction_id,
                'order_id' => $orderId,
                'amount' => $transaction->amount,
            ]);

        } catch (\Throwable $e) {
            $this->error('Error processing message: ' . $e->getMessage());
            Log::error('Error processing transaction message', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function decodePayload(string $message): array
    {
        // Try MessagePack first (binary format)
        if ($this->isBinary($message)) {
            try {
                $decoded = MessagePack::unpack($message);
                return is_array($decoded) ? $decoded : [];
            } catch (\Throwable $e) {
                Log::warning('MessagePack decode failed, trying JSON fallback', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to JSON
        $decoded = json_decode($message, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['raw' => $message];
    }

    protected function isBinary(string $data): bool
    {
        // MessagePack binary data won't be valid UTF-8
        return !mb_check_encoding($data, 'UTF-8') || 
               (ord($data[0]) >= 0x80 && ord($data[0]) <= 0x9f) ||
               ord($data[0]) >= 0xa0;
    }

    protected function calculateFee(float $amount): float
    {
        // Use the Setting model's fee calculation method
        return Setting::calculateTransactionFee($amount);
    }

    protected function resolveTimestamp(array $payload): ?Carbon
    {
        $timestampFields = ['transaction_time','paid_at', 'timestamp', 'created_at', 'time'];
        
        foreach ($timestampFields as $field) {
            if (isset($payload[$field])) {
                try {
                    return Carbon::parse($payload[$field]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to parse transaction timestamp', [
                        'field' => $field,
                        'value' => $payload[$field],
                    ]);
                }
            }
        }

        return null;
    }
}
