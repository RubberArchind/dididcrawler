# Email Notification System - DIDID Claw Machine

This system automatically sends email notifications to users when they receive new transactions or payouts.

## ✅ What's Been Implemented

### 1. Mail Configuration
- **SMTP Setup**: Configured to use `esp32.tonl.ink` SMTP server
- **SSL Encryption**: Secure email sending on port 465
- **From Address**: `info@esp32.tonl.ink`
- **Rate Limiting**: Built-in protection against hosting provider limits

### 2. Email Templates
- **Transaction Email**: Beautiful HTML template for new transaction notifications
- **Payout Email**: Professional template for payout notifications
- **Responsive Design**: Mobile-friendly email layouts

### 3. Automatic Notifications with Safety Features
- **Transaction Observer**: Automatically sends emails when transactions are created/updated to 'success'
- **Payment Observer**: Automatically sends emails when payments are created or marked as 'paid'
- **Queue Support**: Emails are queued for background processing
- **Rate Limiting**: Maximum 4 emails per hour to prevent hosting quota exceeded
- **Error Logging**: Comprehensive logging for debugging
- **Graceful Failure**: System continues working even if email fails

### 4. Hosting Provider Integration
- **cPanel Compatible**: Works with cPanel email systems
- **Quota Management**: Respects hosting provider email limits
- **Automatic Recovery**: Rate limits reset every hour

## 🚨 IMPORTANT: Hosting Email Limits

**Issue Discovered**: Your hosting provider (esp32.tonl.ink) has strict email limits:
- **Maximum**: 5 emails per hour
- **Penalty**: If exceeded, domain gets temporarily blocked from sending emails
- **Reset**: Limits reset every hour

**Our Solution**: 
- Limited to **4 emails per hour** (safely under the 5 limit)
- Rate limiting prevents quota exceeded errors
- Failed emails are logged for review
- System automatically recovers each hour

## 🛠 Available Commands

### Test Mail Configuration
```bash
php artisan mail:test [email]
# Tests SMTP connection and sends a basic test email
```

### Safe Email Testing (Production-Ready)
```bash
php artisan mail:safe-test user@example.com
# Sends test email with rate limiting (counts toward hourly quota)
```

### Check Rate Limit Status
```bash
php artisan mail:rate-status
# Shows current email usage and remaining quota
```

### Check Email Quota
```bash
php artisan mail:check-quota
# Diagnoses email quota and server status
```

### Send Specific Test Emails (Testing Only)
```bash
php artisan email:send-test transaction user@example.com
php artisan email:send-test payout user@example.com
# Sends specific email templates (bypasses rate limiting for testing)
```

### Toggle Mail Driver
```bash
php artisan mail:toggle          # Auto-toggle between smtp and log
php artisan mail:toggle smtp     # Set to SMTP (real emails)
php artisan mail:toggle log      # Set to log (for testing)
```

## 📧 Email Flow with Rate Limiting

### For Transactions:
1. Transaction is created or updated to 'success' status
2. `TransactionObserver` checks rate limit (4/hour max)
3. If under limit: Email is queued and sent, counter incremented
4. If over limit: Email is skipped and logged as warning
5. User receives beautiful transaction notification

### For Payouts:
1. Payment record is created or status changed to 'paid'
2. `PaymentObserver` checks rate limit (4/hour max)  
3. If under limit: Email is queued and sent, counter incremented
4. If over limit: Email is skipped and logged as warning
5. User receives professional payout notification

## 🔧 Production Recommendations

### 1. Increase Hosting Limits
Contact your hosting provider to:
- **Increase hourly email limits** (request 50-100 emails/hour)
- **Add dedicated transactional email quota**
- **Whitelist your application** for higher volumes

### 2. Alternative Email Solutions
If hosting limits remain restrictive:
- **SendGrid**: 100 free emails/day, excellent deliverability
- **Mailgun**: 5,000 free emails/month
- **Amazon SES**: $0.10 per 1,000 emails
- **Postmark**: Specialized for transactional emails

### 3. Monitor Email Performance
```bash
# Check logs for email issues
tail -f storage/logs/laravel.log | grep -i "email\|mail"

# Monitor rate limit usage
php artisan mail:rate-status
```

## 🧪 Testing

### Recommended Testing Sequence
```bash
# 1. Check current quota status
php artisan mail:rate-status

# 2. Send a safe test email (counts toward quota)
php artisan mail:safe-test your@email.com

# 3. Wait 5-10 minutes and check your email
# Check: Inbox, Spam, Promotions, Updates folders

# 4. Check rate limit usage
php artisan mail:rate-status
```

### Email Delivery Troubleshooting
1. **Check ALL email folders** (Spam, Promotions, Updates)
2. **Search for**: "DIDID", "esp32.tonl.ink", or sender email
3. **Wait 5-15 minutes** for delivery
4. **Add sender to contacts**: info@esp32.tonl.ink
5. **Try different email providers** if Gmail blocks

## 📋 Current Status

✅ **Email System**: Fully functional and production-ready  
✅ **Rate Limiting**: Protects against hosting quota exceeded  
✅ **Error Handling**: Comprehensive logging and graceful failures  
✅ **Templates**: Beautiful, responsive email designs  
✅ **Testing Tools**: Multiple commands for different scenarios  
✅ **Hosting Integration**: Works within cPanel email limits  
⚠️ **Delivery Volume**: Limited to 4 emails/hour by hosting provider  

## 💡 Next Steps

1. **Immediate**: Test email delivery with `php artisan mail:safe-test`
2. **Short-term**: Contact hosting provider to increase email limits
3. **Long-term**: Consider dedicated transactional email service for higher volumes

The system is production-ready and will automatically handle email notifications within your hosting constraints!