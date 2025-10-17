<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Message;

class SafeEmailTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:safe-test {email}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email with production rate limiting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔐 Testing email with production-safe rate limiting...");
        $this->newLine();
        
        // Check rate limit first
        $cacheKey = 'email_rate_limit_' . date('Y-m-d-H');
        $emailCount = Cache::get($cacheKey, 0);
        
        $this->line("Current emails sent this hour: {$emailCount}/4");
        
        if ($emailCount >= 4) {
            $this->error("🚫 Rate limit reached! Cannot send email this hour.");
            $this->warn("Wait for the next hour or contact your hosting provider to increase limits.");
            return 1;
        }
        
        try {
            Mail::raw('This is a production-safe test email with rate limiting. Time: ' . now()->format('Y-m-d H:i:s'), function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Safe Test Email - ' . now()->format('H:i:s'))
                        ->from(config('mail.from.address'), 'DIDID Test');
            });
            
            // Increment counter
            Cache::put($cacheKey, $emailCount + 1, now()->addHour());
            
            $this->info("✅ Email sent successfully!");
            $this->line("Emails remaining this hour: " . (3 - $emailCount));
            
            Log::info('Safe test email sent', [
                'recipient' => $email,
                'emails_sent_this_hour' => $emailCount + 1
            ]);
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'exceeded') || str_contains($e->getMessage(), 'limit')) {
                $this->warn("🚨 Your hosting provider blocked the email due to rate limits!");
                $this->line("The hosting limit was hit even though our app limit wasn't.");
                $this->line("This means the hosting limit is lower than our 4/hour setting.");
            }
            
            Log::error('Safe test email failed', [
                'recipient' => $email,
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
        
        return 0;
    }
}