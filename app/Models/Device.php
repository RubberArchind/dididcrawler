<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $activeSubscriptions = $this->subscriptions()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('ends_on')->orWhere('ends_on', '>=', now());
            })
            ->orderBy('ends_on')
            ->get();

        if ($activeSubscriptions->isEmpty()) {
            return 'inactive';
        }

        $nearestExpiry = $activeSubscriptions
            ->filter(fn ($subscription) => $subscription->ends_on !== null)
            ->min('ends_on');

        if ($nearestExpiry && now()->diffInDays($nearestExpiry, false) <= 7) {
            return 'maintenance';
        }

        return 'active';
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
}
