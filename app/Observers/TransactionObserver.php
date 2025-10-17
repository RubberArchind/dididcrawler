<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Mail\NewTransactionNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Only send email for successful transactions
        if ($transaction->isSuccess() && $transaction->user && $transaction->user->email) {
            $this->sendEmailSafely($transaction);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Send email if status changed to success and email hasn't been sent yet
        if ($transaction->wasChanged('status') && 
            $transaction->isSuccess() && 
            $transaction->user && 
            $transaction->user->email) {
            
            $this->sendEmailSafely($transaction);
        }
    }

    /**
     * Send email with rate limiting and error handling
     */
    private function sendEmailSafely(Transaction $transaction): void
    {
        $cacheKey = 'email_rate_limit_' . date('Y-m-d-H');
        $emailCount = Cache::get($cacheKey, 0);
        
        // Limit to 4 emails per hour to stay under hosting limit
        if ($emailCount >= 4) {
            Log::warning('Email rate limit reached, skipping transaction email', [
                'transaction_id' => $transaction->id,
                'user_email' => $transaction->user->email
            ]);
            return;
        }
        
        try {
            // Load the device relationship for the email
            $transaction->load('device');
            
            Mail::to($transaction->user->email)
                ->send(new NewTransactionNotification($transaction));
                
            // Increment email counter
            Cache::put($cacheKey, $emailCount + 1, now()->addHour());
            
            Log::info('Transaction email sent successfully', [
                'transaction_id' => $transaction->id,
                'user_email' => $transaction->user->email
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send transaction email', [
                'transaction_id' => $transaction->id,
                'user_email' => $transaction->user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
