<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Payment;
use App\Models\User;
use App\Models\Device;
use App\Mail\NewTransactionNotification;
use App\Mail\NewPayoutNotification;
use Illuminate\Support\Facades\Mail;

class TestEmailNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-notifications {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email notifications for transactions and payouts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing email notifications to: {$email}");
        $this->newLine();
        
        // Get a user or create a test user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->warn("User with email {$email} not found. Creating a test user...");
            $user = User::create([
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => 'user',
                'account_number' => '1234567890',
                'phone_number' => '+1234567890',
                'address' => 'Test Address',
                'is_active' => true,
            ]);
            $this->info("✅ Test user created successfully.");
        }
        
        // Get a device or create a test device
        $device = Device::first();
        if (!$device) {
            $this->warn("No devices found. Creating a test device...");
            $device = Device::create([
                'user_id' => $user->id,
                'device_uid' => 'TEST123',
                'name' => 'Test Claw Machine',
                'is_active' => true,
            ]);
            $this->info("✅ Test device created successfully.");
        }
        
        $this->newLine();
        $this->info("1. Testing Transaction Email Notification...");
        
        try {
            // Create a test transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'device_id' => $device->id,
                'order_id' => 'order-TEST123-' . rand(100000, 999999),
                'transaction_id' => 'txn_' . time() . '_' . rand(1000, 9999),
                'amount' => 15000,
                'fee_amount' => 1500,
                'net_amount' => 13500,
                'status' => 'success',
                'payment_method' => 'qris',
                'paid_at' => now(),
                'webhook_data' => [
                    'test' => true,
                    'source' => 'email_test'
                ]
            ]);
            
            $this->info("✅ Test transaction created and email should be sent automatically via observer.");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to create test transaction: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("2. Testing Payout Email Notification...");
        
        try {
            // Create a test payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_date' => now()->subDay(),
                'total_omset' => 150000,
                'total_fee' => 15000,
                'net_amount' => 135000,
                'paid_amount' => 135000,
                'status' => 'paid',
                'paid_at' => now(),
                'notes' => 'Test payout for email notification system'
            ]);
            
            $this->info("✅ Test payment created and email should be sent automatically via observer.");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to create test payment: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("3. Testing Direct Email Sending (without database creation)...");
        
        try {
            // Test direct email sending
            $testTransaction = new Transaction([
                'transaction_id' => 'direct_test_' . time(),
                'order_id' => 'order-DIRECT-' . rand(100000, 999999),
                'amount' => 25000,
                'fee_amount' => 2500,
                'net_amount' => 22500,
                'status' => 'success',
                'payment_method' => 'gopay',
                'paid_at' => now(),
            ]);
            $testTransaction->user = $user;
            $testTransaction->device = $device;
            
            Mail::to($email)->send(new NewTransactionNotification($testTransaction));
            $this->info("✅ Direct transaction email sent successfully.");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send direct transaction email: " . $e->getMessage());
        }
        
        try {
            // Test direct payout email
            $testPayment = new Payment([
                'payment_date' => now(),
                'total_omset' => 200000,
                'total_fee' => 20000,
                'net_amount' => 180000,
                'paid_amount' => 180000,
                'status' => 'pending',
                'notes' => 'Direct test payout notification'
            ]);
            $testPayment->user = $user;
            
            Mail::to($email)->send(new NewPayoutNotification($testPayment));
            $this->info("✅ Direct payout email sent successfully.");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send direct payout email: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("📧 Email notification testing completed!");
        $this->info("Please check your inbox at: {$email}");
        $this->warn("Don't forget to check your spam/junk folder if you don't see the emails in your inbox.");
        
        return 0;
    }
}