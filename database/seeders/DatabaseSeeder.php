<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Menambahkan Admin User
        User::create([
            'name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'admin',  // Role admin
        ]);

        // Menambahkan Pengguna Biasa (User)
        User::create([
            'name' => 'Raihan',
            'last_name' => 'Abil',
            'email' => 'abil@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'pelanggan',  // Role user
        ]);

        // Menambahkan lebih banyak pengguna biasa jika diperlukan
        User::create([
            'name' => 'Haekal',
            'last_name' => 'Muhammad',
            'email' => 'haekal@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'pelanggan',  // Role user
        ]);
    }
}
