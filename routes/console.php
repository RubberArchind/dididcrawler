<?php

use App\Console\Commands\ListenForMqttHeartbeats;
use App\Console\Commands\ListenForMqttTransactions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::addCommands([
    ListenForMqttHeartbeats::class,
    ListenForMqttTransactions::class,
]);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
