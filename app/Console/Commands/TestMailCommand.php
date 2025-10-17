<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if (!$email) {
            $email = $this->ask('Enter email address to send test email to:');
        }
        
        $this->info("Testing mail configuration...");
        $this->info("SMTP Host: " . config('mail.mailers.smtp.host'));
        $this->info("SMTP Port: " . config('mail.mailers.smtp.port'));
        $this->info("From Address: " . config('mail.from.address'));
        $this->newLine();
        
        $this->info("Sending test email to: {$email}");
        
        try {
            Mail::raw('This is a test email from DIDID Crawler application. If you receive this, your mail configuration is working correctly!', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - DIDID Crawler Mail Configuration');
            });
            
            $this->info('✅ Test email sent successfully!');
            $this->info('Please check the recipient\'s inbox (and spam folder).');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: ' . $e->getMessage());
            
            // Check if it's a connection issue or recipient issue
            if (str_contains($e->getMessage(), '550')) {
                $this->warn('This appears to be a recipient validation error, not a configuration issue.');
                $this->info('Your SMTP configuration seems to be working - try with a valid email address.');
            }
            
            return 1;
        }
        
        return 0;
    }
}