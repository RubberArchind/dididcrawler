<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Device> */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'device_uid' => $this->faker->unique()->bothify('DEV-#####'),
            'status' => 'dead',
            'tags' => [],
            'last_seen_at' => null,
        ];
    }
}
