<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Device;
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
    public function dashboard(Request $request)
    {
        // Basic stats for cards
        $stats = [
            'total_users' => User::count(),
            'total_transactions' => Transaction::success()->count(),
            'today_transactions' => Transaction::success()->byDate(today())->count(),
            'today_revenue' => (float) Transaction::success()->byDate(today())->sum('net_amount'),
            'pending_payments' => Payment::pending()->count(),
        ];

        // Recent transactions with user relation
        $recent_transactions = Transaction::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recent_transactions'));
    }

    /**
     * Users listing
     */
    public function users(Request $request)
    {
        $users = User::orderByDesc('id')->paginate(15);
        return view('superadmin.users.index', compact('users'));
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        return view('superadmin.users.create');
    }

    /**
     * Store newly created user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:50',
            'address' => 'required|string|max:1000',
            'account_number' => 'required|string|max:100',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'account_number' => $validated['account_number'],
            'role' => 'user',
            'is_active' => true,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('superadmin.users')
            ->with('success', 'User created successfully.');
    }

    /**
     * Record payment for a user and date from the modal form
     */
    public function payUserForDate(Request $request, $user)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $userModel = User::findOrFail($user);

        // Find or create payment record for this user/date
        $payment = Payment::firstOrNew([
            'user_id' => $userModel->id,
            'payment_date' => $date,
        ]);

        // Calculate totals for unpaid transactions
        $unpaidTransactions = Transaction::where('user_id', $userModel->id)
            ->where('status', 'success')
            ->whereDate('paid_at', $date)
            ->whereNull('paid_at')
            ->get();

        $totalOmset = $unpaidTransactions->sum('amount');
        $totalFee = $unpaidTransactions->sum('fee_amount');
        $netAmount = $unpaidTransactions->sum('net_amount');

        $payment->total_omset = $totalOmset;
        $payment->total_fee = $totalFee;
        $payment->net_amount = $netAmount;
        $payment->paid_amount = $validated['paid_amount'];
        $payment->notes = $validated['notes'];
        $payment->status = ($validated['paid_amount'] >= $netAmount) ? 'paid' : 'partial';
        $payment->paid_at = now();
        $payment->save();

        // Mark transactions as paid (set paid_at)
        Transaction::where('user_id', $userModel->id)
            ->where('status', 'success')
            ->whereDate('paid_at', $date)
            ->whereNull('paid_at')
            ->update(['paid_at' => now()]);

        return back()->with('success', 'Payment recorded successfully!');
    }
    // Removed duplicate class definition

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
        $dateInput = $request->get('date');
        $date = $dateInput ? \Carbon\Carbon::parse($dateInput) : today();
        
        // Get last payment cutoff time for the date
        $lastPayment = Payment::where('payment_date', $date->format('Y-m-d'))
            ->where('status', 'paid')
            ->latest('paid_at')
            ->first();
        
        $cutoffTime = $lastPayment ? $lastPayment->paid_at : $date->copy()->startOfDay();
        
        // Get users with transactions grouped by paid/unpaid status
        $usersWithTransactions = User::whereHas('transactions', function($query) use ($date, $cutoffTime) {
                $query->where('status', 'success')
                    ->whereDate('paid_at', $date->format('Y-m-d'));
            })
            ->with(['transactions' => function($query) use ($date) {
                $query->where('status', 'success')
                    ->whereDate('paid_at', $date->format('Y-m-d'))
                    ->with('device');
            }])
            ->get()
            ->map(function($user) use ($cutoffTime) {
                $allTransactions = $user->transactions;
                
                // Split transactions by payment cutoff
                $paidTransactions = $allTransactions->filter(function($trx) use ($cutoffTime) {
                    return $trx->paid_at <= $cutoffTime;
                });
                
                $unpaidTransactions = $allTransactions->filter(function($trx) use ($cutoffTime) {
                    return $trx->paid_at > $cutoffTime;
                });
                
                // Calculate paid group
                $paidOmset = $paidTransactions->sum('amount');
                $paidFee = $paidTransactions->sum('fee_amount');
                $paidNet = $paidTransactions->sum('net_amount');
                
                // Calculate unpaid group
                $unpaidOmset = $unpaidTransactions->sum('amount');
                $unpaidFee = $unpaidTransactions->sum('fee_amount');
                $unpaidNet = $unpaidTransactions->sum('net_amount');
                
                $user->paid_group = [
                    'count' => $paidTransactions->count(),
                    'omset' => $paidOmset,
                    'fee' => $paidFee,
                    'net' => $paidNet,
                    'transactions' => $paidTransactions,
                ];
                
                $user->unpaid_group = [
                    'count' => $unpaidTransactions->count(),
                    'omset' => $unpaidOmset,
                    'fee' => $unpaidFee,
                    'net' => $unpaidNet,
                    'transactions' => $unpaidTransactions,
                ];
                
                $user->total_omset = $paidOmset + $unpaidOmset;
                $user->total_fee = $paidFee + $unpaidFee;
                $user->total_net = $paidNet + $unpaidNet;
                
                return $user;
            });
        
        $stats = [
            'total_omset' => $usersWithTransactions->sum('total_omset'),
            'total_fee' => $usersWithTransactions->sum('total_fee'),
            'total_net' => $usersWithTransactions->sum('total_net'),
            'paid_net' => $usersWithTransactions->sum('paid_group.net'),
            'unpaid_net' => $usersWithTransactions->sum('unpaid_group.net'),
        ];

        return view('superadmin.payments', compact('usersWithTransactions', 'date', 'cutoffTime', 'stats'));
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
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
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

            // Extract device_uid from order_id and find device
            $deviceUid = Transaction::extractDeviceUidFromOrderId($orderId);
            $device = null;
            
            if ($deviceUid) {
                $device = Device::where('device_uid', $deviceUid)->first();
                
                if (!$device) {
                    Log::warning('Device not found for webhook transaction', [
                        'device_uid' => $deviceUid,
                        'order_id' => $orderId
                    ]);
                }
            }

            // Calculate fee based on settings
            $feeAmount = Setting::calculateTransactionFee($amount);
            $netAmount = $amount - $feeAmount;

            // Create or update transaction
            $transaction = Transaction::updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'device_id' => $device?->id,
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
            // Run the backup command
            Artisan::call('backup:run', ['--only-db' => true]);
            
            // Get the output
            $output = Artisan::output();
            
            // Check if the backup was successful
            if (str_contains($output, 'Successfully copied zip to disk')) {
                return back()->with('success', 'Backup created successfully!');
            } else {
                Log::error('Backup failed with output: ' . $output);
                return back()->with('error', 'Backup failed. Please check the logs for details.');
            }
        } catch (\Exception $e) {
            Log::error('Backup error: ' . $e->getMessage());
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
