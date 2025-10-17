<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewTransactionNotification;
use App\Models\Transaction;
use App\Models\User;

class EmailDeliveryCheckCommand extends Command
{
    protected $signature = 'mail:delivery-check {email}';
    protected $description = 'Comprehensive email delivery testing and troubleshooting';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔍 COMPREHENSIVE EMAIL DELIVERY CHECK");
        $this->newLine();
        
        // Step 1: Configuration verification
        $this->info("📋 1. CONFIGURATION VERIFICATION");
        $mailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
        
        $this->line("Mailer: {$mailer}");
        $this->line("SMTP Host: {$host}");
        $this->line("SMTP Port: {$port}");
        $this->line("Username: {$username}");
        $this->line("From: {$fromName} <{$fromAddress}>");
        $this->newLine();
        
        // Step 2: Test simple email
        $this->info("📧 2. SIMPLE EMAIL TEST");
        try {
            Mail::raw("This is a simple test email sent at " . now()->format('Y-m-d H:i:s'), function ($message) use ($email, $fromAddress, $fromName) {
                $message->to($email)
                        ->from($fromAddress, $fromName)
                        ->subject("🧪 Simple Test - " . now()->format('H:i:s'));
            });
            $this->info("✅ Simple email sent successfully");
        } catch (\Exception $e) {
            $this->error("❌ Simple email failed: " . $e->getMessage());
            return 1;
        }
        $this->newLine();
        
        // Step 3: Test transaction notification email
        $this->info("💰 3. TRANSACTION NOTIFICATION TEST");
        try {
            // Find or create a test user
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::first(); // Use first user as fallback
                if (!$user) {
                    $this->error("No users found in database for testing");
                    return 1;
                }
            }
            
            // Find a recent transaction or create test data
            $transaction = Transaction::where('user_id', $user->id)
                                    ->where('status', 'success')
                                    ->first();
            
            if (!$transaction) {
                $this->warn("No successful transactions found for user, creating test data...");
                // Create test transaction data for email preview
                $transaction = new Transaction([
                    'user_id' => $user->id,
                    'order_id' => 'order-TEST-' . time(),
                    'transaction_id' => 'test-' . time(),
                    'amount' => 25000,
                    'fee_amount' => 1250,
                    'net_amount' => 23750,
                    'status' => 'success',
                    'payment_method' => 'qris',
                    'paid_at' => now(),
                ]);
                $transaction->user = $user; // Set relationship manually
            } else {
                $transaction->load('user', 'device');
            }
            
            // Override email for testing
            $originalEmail = $transaction->user->email;
            $transaction->user->email = $email;
            
            Mail::to($email)->send(new NewTransactionNotification($transaction));
            
            // Restore original email
            $transaction->user->email = $originalEmail;
            
            $this->info("✅ Transaction notification sent successfully");
            $this->line("   Amount: Rp " . number_format($transaction->amount, 0, ',', '.'));
            $this->line("   Net: Rp " . number_format($transaction->net_amount, 0, ',', '.'));
        } catch (\Exception $e) {
            $this->error("❌ Transaction notification failed: " . $e->getMessage());
        }
        $this->newLine();
        
        // Step 4: Different subject line test (to bypass spam filters)
        $this->info("🎯 4. ALTERNATIVE SUBJECT TEST");
        try {
            $subjects = [
                "Hi there! Quick update",
                "Your payment confirmation",
                "Transaction completed successfully",
                "Payment received - Thank you!",
                "Update from " . config('app.name'),
            ];
            
            $randomSubject = $subjects[array_rand($subjects)];
            
            Mail::raw("This email uses a different subject line to test spam filter sensitivity.\n\nSubject: {$randomSubject}\nTime: " . now()->format('Y-m-d H:i:s'), function ($message) use ($email, $fromAddress, $fromName, $randomSubject) {
                $message->to($email)
                        ->from($fromAddress, $fromName)
                        ->subject($randomSubject);
            });
            $this->info("✅ Alternative subject email sent: '{$randomSubject}'");
        } catch (\Exception $e) {
            $this->error("❌ Alternative subject failed: " . $e->getMessage());
        }
        $this->newLine();
        
        // Step 5: Delivery troubleshooting guide
        $this->info("🕵️ 5. DELIVERY TROUBLESHOOTING GUIDE");
        $this->line("If emails aren't arriving, check in this order:");
        $this->newLine();
        
        $this->warn("📱 GMAIL USERS:");
        $this->line("• Check ALL folders: Primary, Social, Promotions, Updates, Spam");
        $this->line("• Search for: '{$fromAddress}' or 'DIDID' or '{$fromName}'");
        $this->line("• Add {$fromAddress} to your contacts");
        $this->line("• Wait 10-30 minutes (Gmail delays new senders)");
        $this->newLine();
        
        $this->warn("📧 OTHER EMAIL PROVIDERS:");
        $this->line("• Check spam/junk folder thoroughly");
        $this->line("• Search entire mailbox for sender address");
        $this->line("• Check email filters/rules");
        $this->line("• Whitelist {$fromAddress}");
        $this->newLine();
        
        $this->warn("🔧 TECHNICAL CHECKS:");
        $this->line("• Domain reputation: Check {$fromAddress} domain");
        $this->line("• SMTP logs: Check {$host} delivery reports");
        $this->line("• SPF/DKIM: Verify domain authentication");
        $this->line("• Blacklist: Check if sender IP is blacklisted");
        $this->newLine();
        
        $this->info("✨ SUCCESS TIPS:");
        $this->line("• Ask recipient to check ALL email folders");
        $this->line("• Try sending to different email providers (Gmail, Outlook, Yahoo)");
        $this->line("• Consider using a dedicated email service (SendGrid, Mailgun)");
        $this->line("• Monitor delivery rates and adjust sending practices");
        
        return 0;
    }
}