<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Console\Command;

class RecalculateTransactionFees extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'transactions:recalculate-fees {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate transaction fees based on current settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $transactions = Transaction::where('status', 'success')->get();
        
        if ($transactions->isEmpty()) {
            $this->info('No transactions found to recalculate.');
            return;
        }

        $this->info("Found {$transactions->count()} transactions to process");
        
        $updated = 0;
        $bar = $this->output->createProgressBar($transactions->count());
        $bar->start();

        foreach ($transactions as $transaction) {
            $oldFee = $transaction->fee_amount;
            $newFee = Setting::calculateTransactionFee($transaction->amount);
            $newNetAmount = $transaction->amount - $newFee;
            
            if ($oldFee != $newFee) {
                if (!$dryRun) {
                    $transaction->update([
                        'fee_amount' => $newFee,
                        'net_amount' => $newNetAmount,
                    ]);
                }
                $updated++;
                
                if ($dryRun) {
                    $this->newLine();
                    $this->line("Transaction {$transaction->transaction_id}:");
                    $this->line("  Amount: Rp " . number_format($transaction->amount, 0, ',', '.'));
                    $this->line("  Old Fee: Rp " . number_format($oldFee, 0, ',', '.'));
                    $this->line("  New Fee: Rp " . number_format($newFee, 0, ',', '.'));
                    $this->line("  Old Net: Rp " . number_format($transaction->net_amount, 0, ',', '.'));
                    $this->line("  New Net: Rp " . number_format($newNetAmount, 0, ',', '.'));
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("Would update {$updated} transactions");
            $this->info('Run without --dry-run to apply changes');
        } else {
            $this->info("Updated {$updated} transactions successfully");
        }
    }
}