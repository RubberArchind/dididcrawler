<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupSystemCommand extends Command
{
    protected $signature = 'system:backup {--only-db : Only backup the database}';
    protected $description = 'Create a backup of the system';

    public function handle()
    {
        $this->info('Starting backup...');

        try {
            if ($this->option('only-db')) {
                $this->info('Creating database-only backup...');
                $this->call('backup:run', ['--only-db' => true]);
            } else {
                $this->info('Creating full system backup...');
                $this->call('backup:run');
            }

            $this->info('Backup completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}