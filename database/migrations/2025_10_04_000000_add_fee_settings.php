<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Add fee settings
        Setting::set('fee_percentage', 2.5, 'decimal', 'Global fee percentage for transactions', 'fees');
        Setting::set('minimum_fee', 1000, 'integer', 'Minimum fee amount per transaction', 'fees');
        Setting::set('maximum_fee', 10000, 'integer', 'Maximum fee amount per transaction', 'fees');
    }

    public function down(): void
    {
        // Remove fee settings
        Setting::whereIn('key', ['fee_percentage', 'minimum_fee', 'maximum_fee'])->delete();
    }
};