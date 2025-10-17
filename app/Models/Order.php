<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'external_order_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'amount',
        'description',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for this order
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'order_id', 'order_id');
    }

    /**
     * Generate unique order number for user
     */
    public static function generateOrderNumber($userId)
    {
        $prefix = 'ORD';
        $userCode = str_pad($userId, 3, '0', STR_PAD_LEFT);
        $date = now()->format('Ymd');
        $sequence = self::where('user_id', $userId)
                        ->whereDate('created_at', today())
                        ->count() + 1;
        
        return $prefix . $userCode . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
