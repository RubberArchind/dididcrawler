<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Mail\Message;

class DebugMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:debug {email}';

    /**
     * The console command description.
     */
    protected $description = 'Debug mail configuration and test SMTP connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔍 Debugging mail configuration...");
        $this->newLine();
        
        // Show current mail configuration
        $this->info("Current Mail Configuration:");
        $this->line("Driver: " . config('mail.default'));
        $this->line("Host: " . config('mail.mailers.smtp.host'));
        $this->line("Port: " . config('mail.mailers.smtp.port'));
        $this->line("Username: " . config('mail.mailers.smtp.username'));
        $this->line("From Address: " . config('mail.from.address'));
        $this->line("From Name: " . config('mail.from.name'));
        $this->newLine();
        
        // Test 1: Basic Laravel Mail
        $this->info("📧 Test 1: Basic Laravel Mail...");
        try {
            Mail::raw('This is a debug test email from Laravel.', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Debug Test - Laravel Mail');
            });
            $this->info("✅ Laravel mail sent successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Laravel mail failed: " . $e->getMessage());
            $this->newLine();
            return 1;
        }
        
        // Test 2: Check if emails are being queued
        $this->info("📋 Test 2: Checking queue status...");
        $queueConnection = config('queue.default');
        $this->line("Queue Connection: " . $queueConnection);
        
        if ($queueConnection === 'database') {
            try {
                $jobCount = DB::table('jobs')->count();
                $this->line("Pending jobs in queue: " . $jobCount);
                
                if ($jobCount > 0) {
                    $this->warn("There are pending jobs in the queue. Run 'php artisan queue:work' to process them.");
                }
            } catch (\Exception $e) {
                $this->error("Failed to check queue: " . $e->getMessage());
            }
        }
        
        // Test 3: Send with detailed logging
        $this->newLine();
        $this->info("📝 Test 3: Sending with detailed logging...");
        
        try {
            // Enable mail logging temporarily
            config(['mail.default' => 'log']);
            
            Mail::raw('This is a logged test email.', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Debug Test - Logged Email');
            });
            
            $this->info("✅ Email logged successfully!");
            $this->info("Check storage/logs/laravel.log for the email content.");
            
        } catch (\Exception $e) {
            $this->error("❌ Logging failed: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("🔍 Debug completed!");
        $this->warn("If Laravel mail 'succeeded' but you didn't receive the email, check:");
        $this->line("1. Your spam/junk folder");
        $this->line("2. SMTP server logs on esp32.tonl.ink");
        $this->line("3. Email delivery reputation of your domain");
        $this->line("4. Recipient email server blocking");
        
        return 0;
    }
}