<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $username = env('APP_ADMIN_USERNAME', 'admin');
        $name = env('APP_ADMIN_NAME', 'Administrator');
        $email = env('APP_ADMIN_EMAIL', 'admin@local.test');
        $password = env('APP_ADMIN_PASSWORD', 'admin123');

        $user = User::query()->updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'level' => 'admin',
                'is_active' => true,
            ]
        );

        if (method_exists($user, 'assignRole') && Schema::hasTable('roles')) {
            $user->syncRoles(['admin']);
        }
    }
}
