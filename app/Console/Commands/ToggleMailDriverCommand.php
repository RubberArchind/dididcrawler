<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ToggleMailDriverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:toggle {driver?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle mail driver between smtp and log, or set to a specific driver';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        $driver = $this->argument('driver');
        
        if (!$driver) {
            // Auto-toggle between smtp and log
            if (strpos($envContent, 'MAIL_MAILER=smtp') !== false) {
                $driver = 'log';
            } else {
                $driver = 'smtp';
            }
        }
        
        if (!in_array($driver, ['smtp', 'log', 'array'])) {
            $this->error("Driver must be one of: smtp, log, array");
            return 1;
        }
        
        // Update the MAIL_MAILER line
        $envContent = preg_replace(
            '/^MAIL_MAILER=.*/m',
            "MAIL_MAILER={$driver}",
            $envContent
        );
        
        file_put_contents($envPath, $envContent);
        
        $this->info("✅ Mail driver changed to: {$driver}");
        
        if ($driver === 'smtp') {
            $this->info("📧 Emails will be sent via SMTP to actual recipients");
            $this->warn("Make sure your SMTP credentials are correct!");
        } elseif ($driver === 'log') {
            $this->info("📝 Emails will be logged to storage/logs/laravel.log");
        } elseif ($driver === 'array') {
            $this->info("🔧 Emails will be stored in memory (useful for testing)");
        }
        
        // Clear config cache
        $this->call('config:clear');
        
        return 0;
    }
}