<?php

namespace App\Observers;

use App\Models\Payment;
use App\Mail\NewPayoutNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Send email notification when payment is created
        if ($payment->user && $payment->user->email) {
            $this->sendEmailSafely($payment);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Send email notification when payment status changes to paid
        if ($payment->wasChanged('status') && 
            $payment->isPaid() && 
            $payment->user && 
            $payment->user->email) {
            
            $this->sendEmailSafely($payment);
        }
        
        // Also send notification when paid_at is set for the first time
        if ($payment->wasChanged('paid_at') && 
            $payment->paid_at && 
            $payment->user && 
            $payment->user->email) {
            
            $this->sendEmailSafely($payment);
        }
    }

    /**
     * Send email with rate limiting and error handling
     */
    private function sendEmailSafely(Payment $payment): void
    {
        $cacheKey = 'email_rate_limit_' . date('Y-m-d-H');
        $emailCount = Cache::get($cacheKey, 0);
        
        // Limit to 4 emails per hour to stay under hosting limit
        if ($emailCount >= 4) {
            Log::warning('Email rate limit reached, skipping payout email', [
                'payment_id' => $payment->id,
                'user_email' => $payment->user->email
            ]);
            return;
        }
        
        try {
            Mail::to($payment->user->email)
                ->send(new NewPayoutNotification($payment));
                
            // Increment email counter
            Cache::put($cacheKey, $emailCount + 1, now()->addHour());
            
            Log::info('Payout email sent successfully', [
                'payment_id' => $payment->id,
                'user_email' => $payment->user->email
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send payout email', [
                'payment_id' => $payment->id,
                'user_email' => $payment->user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
