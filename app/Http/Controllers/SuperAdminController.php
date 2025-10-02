<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    /**
     * SuperAdmin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_transactions' => Transaction::count(),
            'today_transactions' => Transaction::whereDate('created_at', today())->count(),
            'today_revenue' => Transaction::success()->whereDate('paid_at', today())->sum('net_amount'),
            'pending_payments' => Payment::pending()->count(),
        ];

        $recent_transactions = Transaction::with('user', 'order')
            ->latest()
            ->take(10)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recent_transactions'));
    }

    /**
     * User Management
     */
    public function users()
    {
        $users = User::where('role', 'user')->paginate(20);
        return view('superadmin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('superadmin.users.create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users|max:255',
            'email' => 'required|email|unique:users',
            'address' => 'required|string|max:500',
            'account_number' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'account_number' => $validated['account_number'],
            'phone_number' => $validated['phone_number'],
            'role' => 'user',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('superadmin.users')->with('success', 'User created successfully!');
    }

    /**
     * Reports
     */
    public function reports(Request $request)
    {
        $date = $request->get('date', today());
        
        $daily_report = Transaction::success()
            ->whereDate('paid_at', $date)
            ->with('user')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(fee_amount) as total_fee'),
                DB::raw('SUM(net_amount) as net_amount')
            )
            ->groupBy('user_id')
            ->get()
            ->load('user');

        $total_stats = [
            'total_transactions' => $daily_report->sum('transaction_count'),
            'total_amount' => $daily_report->sum('total_amount'),
            'total_fee' => $daily_report->sum('total_fee'),
            'net_amount' => $daily_report->sum('net_amount'),
        ];

        // Weekly data for chart
        $weekly_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date_check = now()->subDays($i);
            $daily_amount = Transaction::success()
                ->whereDate('paid_at', $date_check)
                ->sum('net_amount');
                
            $daily_count = Transaction::success()
                ->whereDate('paid_at', $date_check)
                ->count();
                
            // In development, if no transactions are found, add sample data
            // Remove this in production!
            if (app()->environment('local') && $daily_count == 0) {
                // Generate random sample data for development/testing
                $daily_amount = mt_rand(500000, 10000000);
                $daily_count = mt_rand(5, 30);
            }
            
            $weekly_data[] = [
                'date' => $date_check->format('d M'),
                'day' => $date_check->format('D'),
                'total_amount' => $daily_amount,
                'count' => $daily_count,
            ];
        }

        return view('superadmin.reports', compact('daily_report', 'total_stats', 'date', 'weekly_data'));
    }

    /**
     * Payment Management
     */
    public function payments(Request $request)
    {
        $date = $request->get('date', today());
        
        $payments = Payment::with('user')
            ->where('payment_date', $date)
            ->get();

        // Create payments for today if they don't exist
        if ($payments->isEmpty()) {
            $this->generateDailyPayments($date);
            $payments = Payment::with('user')
                ->where('payment_date', $date)
                ->get();
        }

        return view('superadmin.payments', compact('payments', 'date'));
    }

    public function markAsPaid(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'paid_amount' => $validated['paid_amount'],
            'status' => $validated['paid_amount'] >= $payment->net_amount ? 'paid' : 'partial',
            'paid_at' => now(),
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Payment marked as paid successfully!');
    }

    /**
     * Settings Management
     */
    public function settings()
    {
        // Get all settings and convert to a keyed object for easy access
        $settingsCollection = Setting::all();
        $settings = (object) $settingsCollection->pluck('value', 'key')->toArray();
        
        // Add the updated_at from the most recently updated setting
        $latestSetting = $settingsCollection->sortByDesc('updated_at')->first();
        $settings->updated_at = $latestSetting ? $latestSetting->updated_at : null;
        
        return view('superadmin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Webhook Handler
     */
    public function webhook(Request $request)
    {
        // Log webhook data for debugging
        Log::info('Webhook received:', $request->all());

        try {
            // Extract webhook data (adjust based on your payment provider)
            $data = $request->all();
            $transactionId = $data['transaction_id'] ?? null;
            $status = $data['status'] ?? null;
            $amount = $data['amount'] ?? 0;
            $orderId = $data['order_id'] ?? null;

            if (!$transactionId || !$orderId) {
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            // Find or create order
            $order = Order::where('external_order_id', $orderId)->first();
            if (!$order) {
                // Create order if it doesn't exist (you may want to adjust this logic)
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Calculate fee
            $feePercentage = Setting::getGlobalMdrFee();
            $feeAmount = ($amount * $feePercentage) / 100;
            $netAmount = $amount - $feeAmount;

            // Create or update transaction
            $transaction = Transaction::updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'fee_amount' => $feeAmount,
                    'net_amount' => $netAmount,
                    'status' => $status,
                    'payment_method' => $data['payment_method'] ?? null,
                    'webhook_data' => $data,
                    'paid_at' => $status === 'success' ? now() : null,
                ]
            );

            // Send email notification if transaction is successful
            if ($status === 'success' && Setting::get('email_notifications', true)) {
                $this->sendTransactionNotification($transaction);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook processing error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Backup Management
     */
    public function backup()
    {
        try {
            $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/');
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            // Simple database backup (you might want to use a more sophisticated method)
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.host'),
                config('database.connections.mysql.database'),
                $path . $filename
            );

            exec($command, $output, $return_var);

            if ($return_var === 0) {
                return back()->with('success', "Backup created successfully: {$filename}");
            } else {
                return back()->with('error', 'Backup failed');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function maintenance()
    {
        try {
            // Clear various Laravel caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return back()->with('success', 'System cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Maintenance failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper Methods
     */
    private function generateDailyPayments($date)
    {
        $users = User::where('role', 'user')->get();
        
        foreach ($users as $user) {
            $dailyTransactions = Transaction::success()
                ->where('user_id', $user->id)
                ->whereDate('paid_at', $date)
                ->get();

            if ($dailyTransactions->isNotEmpty()) {
                $totalOmset = $dailyTransactions->sum('amount');
                $totalFee = $dailyTransactions->sum('fee_amount');
                $netAmount = $dailyTransactions->sum('net_amount');

                Payment::create([
                    'user_id' => $user->id,
                    'payment_date' => $date,
                    'total_omset' => $totalOmset,
                    'total_fee' => $totalFee,
                    'net_amount' => $netAmount,
                    'status' => 'pending',
                ]);
            }
        }
    }

    private function sendTransactionNotification($transaction)
    {
        try {
            // Simple email notification (you can create a proper Mailable class)
            $user = $transaction->user;
            $subject = 'Transaction Success - ' . Setting::get('company_name', 'DididCrawler');
            
            $message = "Dear {$user->name},\n\n";
            $message .= "Your transaction has been successfully processed:\n";
            $message .= "Transaction ID: {$transaction->transaction_id}\n";
            $message .= "Amount: Rp " . number_format($transaction->amount, 0, ',', '.') . "\n";
            $message .= "Fee: Rp " . number_format($transaction->fee_amount, 0, ',', '.') . "\n";
            $message .= "Net Amount: Rp " . number_format($transaction->net_amount, 0, ',', '.') . "\n";
            $message .= "Time: " . $transaction->paid_at->format('d/m/Y H:i:s') . "\n\n";
            $message .= "Thank you for using our service.\n\n";
            $message .= "Best regards,\n" . Setting::get('company_name', 'DididCrawler');

            // You would replace this with proper Mail facade usage
            mail($user->email, $subject, $message);
            
        } catch (\Exception $e) {
            Log::error('Email notification failed:', ['error' => $e->getMessage()]);
        }
    }
}
