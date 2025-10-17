<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EmailTroubleshootCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:troubleshoot';

    /**
     * The console command description.
     */
    protected $description = 'Comprehensive email troubleshooting checklist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔧 EMAIL TROUBLESHOOTING CHECKLIST");
        $this->newLine();
        
        // Check 1: Configuration
        $this->info("✅ 1. CONFIGURATION CHECK");
        $this->line("Mail Driver: " . config('mail.default'));
        $this->line("SMTP Host: " . config('mail.mailers.smtp.host'));
        $this->line("SMTP Port: " . config('mail.mailers.smtp.port'));
        $this->line("Encryption: " . (config('mail.mailers.smtp.encryption') ?: 'Not set'));
        $this->line("From Email: " . config('mail.from.address'));
        $this->newLine();
        
        // Check 2: Test different email providers
        $this->info("📧 2. TEST WITH DIFFERENT EMAIL PROVIDERS");
        $this->line("Try sending test emails to:");
        $this->line("• Gmail: yourname@gmail.com");
        $this->line("• Outlook: yourname@outlook.com or @hotmail.com");
        $this->line("• Yahoo: yourname@yahoo.com");
        $this->line("• Temp email: Use a temporary email service");
        $this->newLine();
        
        // Check 3: Email authentication
        $this->info("🔐 3. EMAIL AUTHENTICATION (Advanced)");
        $this->line("Your domain (esp32.tonl.ink) may need:");
        $this->line("• SPF Record: 'v=spf1 a mx ~all'");
        $this->line("• DKIM Signing: Digital signatures for emails");
        $this->line("• DMARC Policy: Email authentication protocol");
        $this->newLine();
        
        // Check 4: Gmail-specific issues
        $this->info("📮 4. GMAIL-SPECIFIC ISSUES");
        $this->line("Gmail often filters emails from new senders:");
        $this->line("• Check ALL folders: Inbox, Spam, Promotions, Updates, Social");
        $this->line("• Search for: 'esp32.tonl.ink' or 'DIDID'");
        $this->line("• Gmail may take 5-30 minutes to deliver new sender emails");
        $this->line("• Try adding info@esp32.tonl.ink to your contacts first");
        $this->newLine();
        
        // Check 5: Server reputation
        $this->info("🌐 5. SERVER REPUTATION CHECK");
        $this->line("Check if your server is blacklisted:");
        $this->line("• Visit: https://mxtoolbox.com/blacklists.aspx");
        $this->line("• Enter: esp32.tonl.ink or " . gethostbyname('esp32.tonl.ink'));
        $this->line("• Check mail server reputation");
        $this->newLine();
        
        // Check 6: Alternative solutions
        $this->info("🔄 6. ALTERNATIVE SOLUTIONS");
        $this->line("If deliverability issues persist:");
        $this->line("• Use a transactional email service (SendGrid, Mailgun, SES)");
        $this->line("• Set up proper domain authentication");
        $this->line("• Use a dedicated IP for email sending");
        $this->newLine();
        
        $this->warn("💡 IMMEDIATE ACTIONS:");
        $this->line("1. Check your spam folder thoroughly");
        $this->line("2. Wait 10-15 minutes and check again");
        $this->line("3. Try sending to a different email provider");
        $this->line("4. Add info@esp32.tonl.ink to your email contacts");
        $this->line("5. Search your entire email for 'DIDID' or 'esp32'");
        
        return 0;
    }
}