<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@travelgo.test']);
        $admin->forceFill([
            'name'     => 'Admin',
            'password' => 'admin12345',
            'role'     => 'admin',
        ])->save();
    }
}