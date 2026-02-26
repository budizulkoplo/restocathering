<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('layouts.navigation', function ($view) {
            try {
                $user = Auth::user();
                
                // Log untuk debugging
                Log::info('User Level: ' . ($user->level ?? 'null'));
                
                $menus = Menu::whereNull('parent_id')
                    ->orderBy('seq')
                    ->with(['children' => function($query) {
                        $query->orderBy('seq');
                    }])
                    ->get();
                
                // Log jumlah menu
                Log::info('Total Menus: ' . $menus->count());
                
                // Dump ke file log untuk melihat data
                foreach($menus as $menu) {
                    Log::info('Menu: ' . $menu->name . ', Role: ' . $menu->role);
                }
                
                $view->with('menus', $menus);
                
            } catch (\Exception $e) {
                Log::error('Error in navigation composer: ' . $e->getMessage());
                $view->with('menus', collect([]));
            }
        });
    }

    public function register()
    {
        require_once __DIR__ . '/../Http/Helpers/Navigation.php';
    }
}