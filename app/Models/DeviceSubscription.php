<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'subscription_name',
        'plan',
        'starts_on',
        'ends_on',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
