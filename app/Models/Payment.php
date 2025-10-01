<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_date',
        'total_omset',
        'total_fee',
        'net_amount',
        'paid_amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'total_omset' => 'decimal:2',
            'total_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is fully paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute()
    {
        return $this->net_amount - $this->paid_amount;
    }

    /**
     * Scope for payments by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('payment_date', $date);
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
