<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'device_id',
        'transaction_id',
        'amount',
        'fee_amount',
        'net_amount',
        'status',
        'payment_method',
        'webhook_data',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'webhook_data' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that owns the transaction
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /**
     * Get the device associated with the transaction
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Extract device UID from order_id format: order-{device_uid}-{random}
     */
    public static function extractDeviceUidFromOrderId(string $orderId): ?string
    {
        // Format: order-TEST123-608975
        $parts = explode('-', $orderId);
        
        if (count($parts) >= 3 && $parts[0] === 'order') {
            return $parts[1]; // Return TEST123
        }
        
        return null;
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Scope for successful transactions
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for transactions by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('paid_at', $date);
    }
}
