<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tnmshop.local'],
            [
                'name' => 'TNM Admin',
                'password' => 'admin1234',
                'is_admin' => true,
            ]
        );
    }
}
