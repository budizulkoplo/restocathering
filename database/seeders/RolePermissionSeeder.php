<?php

namespace Database\Seeders;

use App\Models\RestaurantProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'customers.view',
            'ingredients.view',
            'menu-items.view',
            'catering-orders.view',
            'restaurant-orders.view',
            'dining-tables.view',
            'purchases.view',
            'stock-opnames.view',
            'reports.view',
            'settings.view',
            'users.view',
            'roles.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'admin' => $permissions,
            'manager' => [
                'dashboard.view',
                'customers.view',
                'ingredients.view',
                'menu-items.view',
                'catering-orders.view',
                'restaurant-orders.view',
                'dining-tables.view',
                'purchases.view',
                'stock-opnames.view',
                'reports.view',
                'settings.view',
            ],
            'cashier' => [
                'dashboard.view',
                'customers.view',
                'menu-items.view',
                'catering-orders.view',
                'restaurant-orders.view',
                'dining-tables.view',
            ],
            'kitchen' => [
                'dashboard.view',
                'ingredients.view',
                'menu-items.view',
                'catering-orders.view',
                'purchases.view',
                'stock-opnames.view',
            ],
        ];

        foreach ($roles as $name => $grants) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($grants);
        }

        RestaurantProfile::query()->firstOrCreate(
            ['name' => 'Resto Catering'],
            [
                'phone' => '-',
                'address' => '-',
                'description' => 'Sistem operasional resto dan catering.',
            ]
        );

        if (Schema::hasTable('users')) {
            $user = User::query()->first();

            if ($user) {
                $user->syncRoles(['admin']);
            }
        }
    }
}
