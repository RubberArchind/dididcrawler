<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class CheckEmailQuotaCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:check-quota';

    /**
     * The console command description.
     */
    protected $description = 'Check if email sending is currently allowed and test with minimal sends';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Checking email quota and server status...");
        $this->newLine();
        
        // Test with a very simple email first
        $this->info("📧 Testing with minimal email to check quota...");
        
        try {
            // Use a simple text email to minimize chance of being flagged
            Mail::raw('Quota test - ' . time(), function (Message $message) {
                $message->to('test@example.com') // This will fail but won't count against real recipients
                        ->subject('Quota Check')
                        ->from(config('mail.from.address'), 'System Check');
            });
            
            $this->info("✅ Email system is responding (even if delivery fails)");
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'exceeded') || str_contains($errorMessage, 'limit')) {
                $this->error("❌ Email quota exceeded! Wait for hourly reset.");
                $this->warn("Your hosting provider has temporarily blocked email sending.");
                $this->line("Solutions:");
                $this->line("1. Wait 30-60 minutes for the hourly limit to reset");
                $this->line("2. Contact your hosting provider to increase limits");
                $this->line("3. Consider using a transactional email service");
                return 1;
            } else {
                $this->error("❌ Other email error: " . $errorMessage);
            }
        }
        
        $this->newLine();
        $this->info("💡 Recommendations for production:");
        $this->line("1. 📈 Increase email limits with your hosting provider");
        $this->line("2. 🔄 Implement email queue with retry logic");
        $this->line("3. 📊 Add email sending rate limiting");
        $this->line("4. 🚀 Consider transactional email services (SendGrid, Mailgun)");
        
        return 0;
    }
}