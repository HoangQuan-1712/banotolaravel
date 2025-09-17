<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo admin thủ công (email: admin@example.com, pass: password)
        User::create([
            'name' => 'Admin',
            'email' => 'muoinhungnhat@gmail.com',
            'password' => Hash::make('1'),
            'role' => 'admin',
        ]);

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
};
