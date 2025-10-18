<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    /**
     * User Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $stats = [
            'today_transactions' => Transaction::where('user_id', $user->id)
                ->whereDate('paid_at', today())
                ->success()
                ->count(),
            'today_revenue' => Transaction::where('user_id', $user->id)
                ->whereDate('paid_at', today())
                ->success()
                ->sum('net_amount'),
            'month_revenue' => Transaction::where('user_id', $user->id)
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->success()
                ->sum('net_amount'),
            'pending_payment' => Payment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('net_amount'),
        ];

        // Recent transactions
        $recent_transactions = Transaction::where('user_id', $user->id)
            ->with('order')
            ->latest()
            ->take(10)
            ->get();

        // Payment status for today
        $today_payment = Payment::where('user_id', $user->id)
            ->where('payment_date', today())
            ->first();

        return view('user.dashboard', compact('stats', 'recent_transactions', 'today_payment'));
    }

    /**
     * Daily Reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', today());
        
        $transactions = Transaction::where('user_id', $user->id)
            ->whereDate('paid_at', $date)
            ->with('order')
            ->get();

        $daily_stats = [
            'total_transactions' => $transactions->count(),
            'successful_transactions' => $transactions->where('status', 'success')->count(),
            'total_amount' => $transactions->sum('amount'),
            'total_fee' => $transactions->sum('fee_amount'),
            'net_amount' => $transactions->sum('net_amount'),
        ];

        // Weekly data for chart
        $weekly_data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date_check = now()->subDays($i);
            $daily_revenue = Transaction::where('user_id', $user->id)
                ->whereDate('paid_at', $date_check)
                ->success()
                ->sum('net_amount');
            
            $daily_count = Transaction::where('user_id', $user->id)
                ->whereDate('paid_at', $date_check)
                ->success()
                ->count();
                
            // In development, if no transactions are found, add sample data
            // Remove this in production!
            if (app()->environment('local') && $daily_count == 0) {
                // Generate random sample data for development/testing
                $daily_revenue = mt_rand(100000, 5000000);
                $daily_count = mt_rand(1, 15);
            }
            
            $weekly_data[] = [
                'date' => $date_check->format('d M'), // Short date format for readability 
                'day' => $date_check->format('D'),
                'total_amount' => $daily_revenue,
                'count' => $daily_count,
            ];
        }

        return view('user.reports', compact('transactions', 'daily_stats', 'weekly_data', 'date'));
    }

    /**
     * Payment Status
     */
    public function payments(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->format('Y-m'));
        
        [$year, $monthNum] = explode('-', $month);
        
        $payments = Payment::where('user_id', $user->id)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $monthNum)
            ->orderBy('payment_date', 'desc')
            ->get();

        $monthly_stats = [
            'total_payments' => $payments->count(),
            'paid_count' => $payments->where('status', 'paid')->count(),
            'total_amount' => $payments->sum('net_amount'),
            'paid_amount' => $payments->sum('paid_amount'),
        ];

        return view('user.payments', compact('payments', 'monthly_stats', 'month'));
    }

    /**
     * User Devices List
     */
    public function devices(Request $request)
    {
        $user = Auth::user();
        
        $devices = Device::where('user_id', $user->id)
            ->with('subscriptions')
            ->orderBy('device_uid', 'asc')
            ->get();

        return view('user.devices', compact('devices'));
    }
}
