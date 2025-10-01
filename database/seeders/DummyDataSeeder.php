<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Handle foreign key constraints for different database types
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }
        
        // Clear existing data
        Transaction::truncate();
        Order::truncate();
        Payment::truncate();
        
        // Re-enable foreign key constraints
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }
        
        echo "Creating dummy data...\n";
        
        // Get users
        $users = User::where('role', 'user')->get();
        
        if ($users->isEmpty()) {
            echo "No users found. Please run UserSeeder first.\n";
            return;
        }
        
        // Get fee percentage
        $feePercentage = Setting::where('key', 'transaction_fee')->first()->value ?? 2.5;
        
        // Generate data for the last 30 days
        $startDate = now()->subDays(30);
        $endDate = now();
        
        $transactionId = 1;
        
        // Loop through each day
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            
            foreach ($users as $user) {
                // Random number of transactions per user per day (0-8)
                $transactionsCount = rand(0, 8);
                
                for ($i = 0; $i < $transactionsCount; $i++) {
                    // Create order
                    $order = Order::create([
                        'user_id' => $user->id,
                        'order_number' => sprintf('%s%04d', $user->username, $transactionId),
                        'external_order_id' => 'EXT_' . time() . '_' . $transactionId,
                        'customer_name' => 'Customer ' . $transactionId,
                        'customer_email' => 'customer' . $transactionId . '@example.com',
                        'customer_phone' => '+628' . rand(100000000, 999999999),
                        'amount' => rand(50000, 500000), // 50k - 500k
                        'description' => 'Payment for services - Order #' . $transactionId,
                        'status' => 'completed',
                        'created_at' => $date->copy()->addMinutes(rand(0, 1440)), // Random time during the day
                        'updated_at' => $date->copy()->addMinutes(rand(0, 1440)),
                    ]);
                    
                    // Random transaction status (90% success, 8% pending, 2% failed)
                    $statusRand = rand(1, 100);
                    if ($statusRand <= 90) {
                        $status = 'success';
                    } elseif ($statusRand <= 98) {
                        $status = 'pending';
                    } else {
                        $status = 'failed';
                    }
                    
                    // Calculate fee (only for successful transactions)
                    $feeAmount = 0;
                    $netAmount = $order->amount;
                    $paidAt = null;
                    
                    if ($status === 'success') {
                        $feeAmount = $order->amount * ($feePercentage / 100);
                        $netAmount = $order->amount - $feeAmount;
                        $paidAt = $order->created_at->copy()->addMinutes(rand(1, 30));
                    }
                    
                    // Generate payment methods
                    $paymentMethods = ['credit_card', 'bank_transfer', 'e_wallet', 'qris', 'virtual_account'];
                    $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                    
                    // Create transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'transaction_id' => 'TXN_' . time() . '_' . $transactionId,
                        'amount' => $order->amount,
                        'fee_amount' => $feeAmount,
                        'net_amount' => $netAmount,
                        'status' => $status,
                        'payment_method' => $paymentMethod,
                        'webhook_data' => json_encode([
                            'payment_method' => $paymentMethod,
                            'bank_code' => $paymentMethod === 'bank_transfer' ? 'BCA' : null,
                            'transaction_time' => $order->created_at->toISOString(),
                        ]),
                        'paid_at' => $paidAt,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
                    
                    $transactionId++;
                }
            }
            
            echo "Generated data for " . $date->format('Y-m-d') . "\n";
        }
        
        echo "Generating daily payments...\n";
        
        // Generate daily payments for successful transactions
        $this->generateDailyPayments($startDate, $endDate);
        
        echo "Dummy data seeding completed!\n";
        echo "Total transactions: " . Transaction::count() . "\n";
        echo "Total orders: " . Order::count() . "\n";
        echo "Total payments: " . Payment::count() . "\n";
    }
    
    private function generateDailyPayments($startDate, $endDate)
    {
        $users = User::where('role', 'user')->get();
        
        for ($date = $startDate->copy(); $date->lte($endDate->subDay()); $date->addDay()) {
            foreach ($users as $user) {
                // Get successful transactions for this user on this date
                $transactions = Transaction::where('user_id', $user->id)
                    ->where('status', 'success')
                    ->whereDate('created_at', $date)
                    ->get();
                
                if ($transactions->isNotEmpty()) {
                    $totalOmset = $transactions->sum('amount');
                    $totalFee = $transactions->sum('fee_amount');
                    $netAmount = $transactions->sum('net_amount');
                    
                    // Random payment status (70% paid, 20% partial, 10% pending)
                    $statusRand = rand(1, 100);
                    if ($statusRand <= 70) {
                        $paymentStatus = 'paid';
                        $paidAmount = $netAmount;
                        $paidAt = $date->copy()->addDay()->addHours(rand(1, 24));
                    } elseif ($statusRand <= 90) {
                        $paymentStatus = 'partial';
                        $paidAmount = $netAmount * (rand(30, 80) / 100);
                        $paidAt = $date->copy()->addDay()->addHours(rand(1, 24));
                    } else {
                        $paymentStatus = 'pending';
                        $paidAmount = 0;
                        $paidAt = null;
                    }
                    
                    // Generate payment notes
                    $notes = null;
                    if ($paymentStatus !== 'pending') {
                        $noteTypes = [
                            'Transfer completed to account ' . $user->account_number,
                            'Payment processed via bank transfer',
                            'Manual transfer - confirmed by admin',
                            'Batch payment processed successfully',
                            'Payment sent to registered account',
                        ];
                        $notes = $noteTypes[array_rand($noteTypes)];
                    }
                    
                    Payment::create([
                        'user_id' => $user->id,
                        'payment_date' => $date,
                        'total_omset' => $totalOmset,
                        'total_fee' => $totalFee,
                        'net_amount' => $netAmount,
                        'paid_amount' => $paidAmount,
                        'status' => $paymentStatus,
                        'paid_at' => $paidAt,
                        'notes' => $notes,
                        'created_at' => $date->copy()->endOfDay(),
                        'updated_at' => $paidAt ?? $date->copy()->endOfDay(),
                    ]);
                }
            }
        }
    }
}