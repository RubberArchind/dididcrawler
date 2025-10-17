<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MessagePack\MessagePack;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;

class ListenForMqttHeartbeats extends Command
{
    protected $signature = 'mqtt:listen-heartbeats {--once : Process a single batch of messages and then exit}';

    protected $description = 'Subscribe to MQTT heartbeat topics and update device activity data.';

    public function handle(): int
    {
        $host = Setting::get('mqtt_host', config('mqtt.host'));
        $port = (int) Setting::get('mqtt_port', config('mqtt.port'));
        $username = Setting::get('mqtt_username', config('mqtt.username'));
        $password = Setting::get('mqtt_password', config('mqtt.password'));
        $prefix = rtrim((string) Setting::get('mqtt_topic_prefix', config('mqtt.topic_prefix')), '/');
        $clientId = sprintf('%s-%s', config('mqtt.client_id', 'dididcrawler-listener'), uniqid());

        $this->info("MQTT Connection Settings:");
        $this->line("Host: $host");
        $this->line("Port: $port");
        $this->line("Username: " . ($username ?: '(none)'));
        $this->line("Password: " . ($password ? str_repeat('*', strlen($password)) : '(none)'));
        $this->line("Topic Prefix: $prefix");
        $this->line("Client ID: $clientId");

        if (blank($host)) {
            $this->error('MQTT host is not configured. Please update the settings page.');
            return self::FAILURE;
        }

        $settings = (new ConnectionSettings())
            ->setUsername($username ?: null)
            ->setPassword($password ?: null)
            ->setKeepAliveInterval(config('mqtt.keep_alive', 60))
            ->setUseTls(false)
            ->setConnectTimeout(config('mqtt.connection_timeout', 30));

        $client = new MqttClient($host, $port, $clientId);

        try {
            $client->connect($settings, true);
        } catch (MqttClientException $exception) {
            $this->error('Failed to connect to MQTT broker: ' . $exception->getMessage());
            Log::error('MQTT connection failed', ['exception' => $exception]);
            return self::FAILURE;
        }

        $topic = sprintf('%s/+/heartbeat', $prefix);
        $this->info(sprintf('Subscribed to heartbeat topic: %s', $topic));

        $client->subscribe($topic, function (string $topic, string $message) {
            $this->processMessage($topic, $message);
        }, 0);

        $loopForever = !$this->option('once');

        try {
            $client->loop($loopForever);
        } catch (MqttClientException $exception) {
            $this->error('MQTT loop error: ' . $exception->getMessage());
            Log::error('MQTT loop failed', ['exception' => $exception]);
            return self::FAILURE;
        } finally {
            try {
                $client->disconnect();
            } catch (MqttClientException $exception) {
                Log::warning('MQTT disconnect error', ['exception' => $exception]);
            }
        }

        return self::SUCCESS;
    }

    protected function processMessage(string $topic, string $message): void
    {
        if ($this->output->isVerbose()) {
            $this->info(sprintf('Heartbeat received on %s', $topic));
        }

        $parts = explode('/', trim($topic, '/'));
        if (count($parts) < 2) {
            Log::warning('MQTT heartbeat topic not recognised', compact('topic'));
            return;
        }

        $deviceUid = $parts[count($parts) - 2];
        $payload = $this->decodePayload($message);
        $reportedAt = $this->resolveTimestamp($payload) ?? now();
        $status = $payload['status'] ?? $payload['state'] ?? null;

        $device = Device::where('device_uid', $deviceUid)->first();

        if (!$device) {
            Log::info('Heartbeat received for unknown device', [
                'device_uid' => $deviceUid,
                'topic' => $topic,
            ]);
            return;
        }

        $heartbeat = $device->recordHeartbeat($payload, $reportedAt, $topic, $status);

        Log::debug('Recorded device heartbeat', [
            'device_id' => $device->id,
            'device_uid' => $device->device_uid,
            'heartbeat_id' => $heartbeat->id,
            'reported_at' => $reportedAt->toIso8601String(),
            'status' => $status,
        ]);
    }

    protected function decodePayload(string $message): array
    {
        // Try MessagePack first (binary format from ESP32)
        if ($this->isBinary($message)) {
            try {
                $decoded = MessagePack::unpack($message);
                
                return $this->normalizeMsgPackData($decoded);
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
        // Also check for common MessagePack markers (0x80-0x8f for fixmap, 0x90-0x9f for fixarray)
        return !mb_check_encoding($data, 'UTF-8') || 
               (ord($data[0]) >= 0x80 && ord($data[0]) <= 0x9f) ||
               ord($data[0]) >= 0xa0;
    }

    protected function normalizeMsgPackData(mixed $data): array
    {
        // Convert to array if needed
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return ['raw' => $data];
        }

        // Map shortened keys back to full names
        $keyMap = [
            'ss' => 'ssid',
            'rs' => 'rssi',
            'up' => 'uptime_ms',
            'nm' => 'nominal',
            'oid' => 'order_id',
        ];

        $normalized = [];
        foreach ($data as $key => $value) {
            $normalizedKey = $keyMap[$key] ?? $key;
            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }

    protected function resolveTimestamp(array $payload): ?Carbon
    {
        if (! isset($payload['timestamp'])) {
            return null;
        }

        try {
            return Carbon::parse($payload['timestamp']);
        } catch (\Throwable $exception) {
            Log::warning('Failed to parse heartbeat timestamp', [
                'timestamp' => $payload['timestamp'],
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
