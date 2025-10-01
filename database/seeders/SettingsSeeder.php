<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'global_mdr_fee',
                'value' => '2.5',
                'type' => 'decimal',
                'description' => 'Global MDR fee percentage (2.5%)',
                'group' => 'payment',
            ],
            [
                'key' => 'backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable daily backup',
                'group' => 'system',
            ],
            [
                'key' => 'backup_time',
                'value' => '02:00',
                'type' => 'string',
                'description' => 'Daily backup time (HH:MM)',
                'group' => 'system',
            ],
            [
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'group' => 'notification',
            ],
            [
                'key' => 'company_name',
                'value' => 'DididCrawler',
                'type' => 'string',
                'description' => 'Company name for reports and emails',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::create($setting);
        }
    }
}
