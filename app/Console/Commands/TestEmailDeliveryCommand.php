<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestEmailDeliveryCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test-delivery {email}';

    /**
     * The console command description.
     */
    protected $description = 'Test email delivery with multiple approaches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🚀 Testing email delivery to: {$email}");
        $this->newLine();
        
        // Test 1: Very simple email
        $this->info("📧 Test 1: Simple text email...");
        try {
            Mail::raw('Hello! This is a simple test email. If you receive this, your email is working!', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Simple Test Email')
                        ->from(config('mail.from.address'), 'Test System');
            });
            $this->info("✅ Simple email sent!");
        } catch (\Exception $e) {
            $this->error("❌ Simple email failed: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Test 2: Email with different subject to avoid spam filters
        $this->info("📧 Test 2: Personal-style email...");
        try {
            $subject = "Hi there - Test message " . date('H:i:s');
            $body = "Hi,\n\nThis is a test email from your DIDID Claw Machine system.\n\nTime sent: " . now()->format('Y-m-d H:i:s') . "\n\nBest regards,\nDIDID System";
            
            Mail::raw($body, function (Message $message) use ($email, $subject) {
                $message->to($email)
                        ->subject($subject)
                        ->from(config('mail.from.address'), 'DIDID Support');
            });
            $this->info("✅ Personal-style email sent!");
        } catch (\Exception $e) {
            $this->error("❌ Personal-style email failed: " . $e->getMessage());
        }
        
        $this->newLine();
        
        // Test 3: Check if it's a delivery delay
        $this->info("📧 Test 3: Priority email...");
        try {
            Mail::raw('URGENT: This is a high-priority test email with unique ID: ' . uniqid(), function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('URGENT - Test Email ' . time())
                        ->from(config('mail.from.address'), 'DIDID Urgent')
                        ->priority(1); // High priority
            });
            $this->info("✅ Priority email sent!");
        } catch (\Exception $e) {
            $this->error("❌ Priority email failed: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("📋 Email delivery troubleshooting tips:");
        $this->line("1. ✉️  Check ALL your email folders (Inbox, Spam, Junk, Promotions, Updates)");
        $this->line("2. ⏰ Wait 5-10 minutes - email delivery can be delayed");
        $this->line("3. 🔍 Search your email for 'DIDID' or 'esp32.tonl.ink'");
        $this->line("4. 📱 Check if you have email on your phone vs web interface");
        $this->line("5. 🏢 Gmail sometimes delays emails from new senders");
        
        $this->newLine();
        $this->warn("If you still don't receive any emails, the issue might be:");
        $this->line("• Your SMTP server (esp32.tonl.ink) might be blacklisted by Gmail");
        $this->line("• Your domain needs SPF/DKIM records");
        $this->line("• Gmail is blocking the sender");
        $this->line("• Try sending to a different email provider (Yahoo, Outlook, etc.)");
        
        return 0;
    }
}