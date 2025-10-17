<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EmailRateLimitStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:rate-status';

    /**
     * The console command description.
     */
    protected $description = 'Check current email rate limit status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("📊 EMAIL RATE LIMIT STATUS");
        $this->newLine();
        
        $currentHour = date('Y-m-d-H');
        $cacheKey = 'email_rate_limit_' . $currentHour;
        $emailCount = Cache::get($cacheKey, 0);
        
        $this->info("Current Hour: " . date('Y-m-d H:i:s'));
        $this->line("Emails Sent This Hour: {$emailCount}/4");
        
        if ($emailCount >= 4) {
            $this->error("🚫 RATE LIMIT REACHED - No more emails will be sent this hour");
            $nextHour = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime($currentHour . ':00:00')));
            $this->warn("Rate limit resets at: {$nextHour}");
        } else {
            $remaining = 4 - $emailCount;
            $this->info("✅ {$remaining} emails remaining this hour");
        }
        
        $this->newLine();
        $this->info("🕐 Rate Limit Schedule:");
        for ($i = 0; $i < 3; $i++) {
            $hour = date('H:i', strtotime('+' . ($i + 1) . ' hour'));
            $this->line("  {$hour} - Rate limit resets (4 emails available)");
        }
        
        $this->newLine();
        $this->warn("💡 To increase limits, contact your hosting provider about:");
        $this->line("• Increasing hourly email send limits");
        $this->line("• Dedicated email quotas for applications");
        $this->line("• Transactional email allowances");
        
        return 0;
    }
}