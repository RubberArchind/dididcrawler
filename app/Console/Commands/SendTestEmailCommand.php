<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\NewTransactionNotification;
use App\Mail\NewPayoutNotification;
use App\Models\Transaction;
use App\Models\Payment;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Mail;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-test {type} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a specific test email (transaction or payout) to an email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->argument('email');
        
        if (!in_array($type, ['transaction', 'payout'])) {
            $this->error("Type must be either 'transaction' or 'payout'");
            return 1;
        }
        
        $this->info("Sending {$type} test email to: {$email}");
        
        try {
            if ($type === 'transaction') {
                $this->sendTransactionEmail($email);
            } else {
                $this->sendPayoutEmail($email);
            }
            
            $this->info("✅ Test {$type} email sent successfully!");
            $this->info("Please check your inbox at: {$email}");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send {$type} email: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function sendTransactionEmail($email)
    {
        // Create test data
        $user = new User([
            'name' => 'John Doe',
            'email' => $email,
            'account_number' => '1234567890'
        ]);
        
        $device = new Device([
            'device_uid' => 'DEMO001',
            'name' => 'Demo Claw Machine #1'
        ]);
        
        $transaction = new Transaction([
            'transaction_id' => 'demo_txn_' . time(),
            'order_id' => 'order-DEMO001-' . rand(100000, 999999),
            'amount' => 20000,
            'fee_amount' => 2000,
            'net_amount' => 18000,
            'status' => 'success',
            'payment_method' => 'qris',
            'paid_at' => now(),
        ]);
        
        // Set relationships
        $transaction->user = $user;
        $transaction->device = $device;
        
        // Send email
        Mail::to($email)->send(new NewTransactionNotification($transaction));
    }
    
    private function sendPayoutEmail($email)
    {
        // Create test data
        $user = new User([
            'name' => 'Jane Smith',
            'email' => $email,
            'account_number' => '9876543210'
        ]);
        
        $payment = new Payment([
            'payment_date' => now()->subDays(1),
            'total_omset' => 500000,
            'total_fee' => 50000,
            'net_amount' => 450000,
            'paid_amount' => 450000,
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => 'Monthly payout for your claw machine revenue'
        ]);
        
        // Set relationship
        $payment->user = $user;
        
        // Send email
        Mail::to($email)->send(new NewPayoutNotification($payment));
    }
}