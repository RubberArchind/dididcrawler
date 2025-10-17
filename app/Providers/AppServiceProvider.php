<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use App\Models\Transaction;
use App\Models\Payment;
use App\Observers\TransactionObserver;
use App\Observers\PaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // Blade directive: @tz($datetime, 'format', 'Asia/Jakarta')
        Blade::directive('tz', function ($expression) {
            return "<?php echo \\App\\Support\\Tz::format($expression); ?>";
        });

        // Register model observers for email notifications
        Transaction::observe(TransactionObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}
