<?php

namespace Tests\Unit;

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function device_records_heartbeat_and_updates_last_seen(): void
    {
        $device = Device::factory()->create([
            'last_seen_at' => null,
        ]);

        $reportedAt = now();
        $payload = [
            'status' => 'online',
            'timestamp' => $reportedAt->toIso8601String(),
        ];

        $heartbeat = $device->recordHeartbeat(
            $payload,
            $reportedAt,
            sprintf('devices/%s/heartbeat', $device->device_uid),
            'online'
        );

        $this->assertEquals($reportedAt->toDateTimeString(), $device->fresh()->last_seen_at?->toDateTimeString());

        $this->assertDatabaseHas('device_heartbeats', [
            'id' => $heartbeat->id,
            'device_id' => $device->id,
            'status' => 'online',
        ]);

        $this->assertSame('active', $device->fresh()->status);
    }

    /** @test */
    public function device_status_transitions_based_on_last_seen(): void
    {
        $activeDevice = Device::factory()->create([
            'last_seen_at' => now()->subHours(12),
        ]);
        $this->assertSame('active', $activeDevice->fresh()->status);

        $idleDevice = Device::factory()->create([
            'last_seen_at' => now()->subHours(30),
        ]);
        $this->assertSame('idle', $idleDevice->fresh()->status);

        $deadDevice = Device::factory()->create([
            'last_seen_at' => now()->subHours(60),
        ]);
        $this->assertSame('dead', $deadDevice->fresh()->status);

        $idleDevice->forceFill(['last_seen_at' => now()->subHours(50)])->saveQuietly();
        $idleDevice->refreshStatus();
        $this->assertSame('dead', $idleDevice->fresh()->status);

        $deadDevice->forceFill(['last_seen_at' => now()->subHours(10)])->saveQuietly();
        $deadDevice->refreshStatus();
        $this->assertSame('active', $deadDevice->fresh()->status);
    }
}
