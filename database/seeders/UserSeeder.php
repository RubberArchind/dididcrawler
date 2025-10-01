<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        \App\Models\User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'admin@dididcrawler.com',
            'address' => 'Jakarta, Indonesia',
            'account_number' => '1234567890',
            'phone_number' => '+62812345678',
            'role' => 'superadmin',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Create multiple sample users
        $users = [
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'email' => 'budi@example.com',
                'address' => 'Jakarta Selatan, DKI Jakarta',
                'account_number' => '1122334455',
                'phone_number' => '+62812111111',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'username' => 'siti',
                'email' => 'siti@example.com',
                'address' => 'Bandung, Jawa Barat',
                'account_number' => '2233445566',
                'phone_number' => '+62812222222',
            ],
            [
                'name' => 'Ahmad Rahman',
                'username' => 'ahmad',
                'email' => 'ahmad@example.com',
                'address' => 'Surabaya, Jawa Timur',
                'account_number' => '3344556677',
                'phone_number' => '+62812333333',
            ],
            [
                'name' => 'Dewi Kusuma',
                'username' => 'dewi',
                'email' => 'dewi@example.com',
                'address' => 'Yogyakarta, DI Yogyakarta',
                'account_number' => '4455667788',
                'phone_number' => '+62812444444',
            ],
            [
                'name' => 'Riko Pratama',
                'username' => 'riko',
                'email' => 'riko@example.com',
                'address' => 'Medan, Sumatera Utara',
                'account_number' => '5566778899',
                'phone_number' => '+62812555555',
            ]
        ];

        foreach ($users as $userData) {
            \App\Models\User::create(array_merge($userData, [
                'role' => 'user',
                'password' => Hash::make('userpass123'),
                'email_verified_at' => now(),
            ]));
        }
    }
}
