<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_uid',
        'user_id',
        'tags',
        'status',
        'last_seen_at',
    ];

    protected static function booted(): void
    {
        static::retrieved(function (Device $device) {
            $device->refreshStatus();
        });

        static::saved(function (Device $device) {
            $device->refreshStatus();
        });
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(DeviceSubscription::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(DeviceHeartbeat::class);
    }

    public function latestHeartbeat(): HasOne
    {
        return $this->hasOne(DeviceHeartbeat::class)->latestOfMany('reported_at');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('user_id');
    }

    public function addTags(array $tags): void
    {
        $current = collect($this->tags ?? []);
        $merged = $current->merge($tags)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values();

        $this->tags = $merged->all();
    }

    public function calculateStatus(): string
    {
        if (! $this->last_seen_at) {
            return 'dead';
        }

        $hoursSinceSeen = $this->last_seen_at->diffInHours(now());

        if ($hoursSinceSeen < 24) {
            return 'active';
        }

        if ($hoursSinceSeen < 48) {
            return 'idle';
        }

        return 'dead';
    }

    public function refreshStatus(): void
    {
        $calculated = $this->calculateStatus();

        if ($this->status !== $calculated) {
            $this->forceFill(['status' => $calculated]);

            if ($this->exists) {
                $this->saveQuietly();
            }
        }
    }

    public function recordHeartbeat(array $payload, Carbon $reportedAt, string $topic, ?string $status = null): DeviceHeartbeat
    {
        if (!$this->exists) {
            $this->save();
        }

        $this->forceFill(['last_seen_at' => $reportedAt])->saveQuietly();

        $this->refreshStatus();

        return $this->heartbeats()->create([
            'topic' => $topic,
            'status' => $status,
            'payload' => $payload,
            'reported_at' => $reportedAt,
        ]);
    }
}
