<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CateringOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RestaurantOrderController;

/*
LOGIN
*/
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/resto/order/{token}', [RestaurantOrderController::class, 'publicOrder'])->name('restaurant-orders.public');
Route::post('/resto/order/{token}', [RestaurantOrderController::class, 'storePublicOrder'])->name('restaurant-orders.public.store');

/*
PROTECTED
*/
Route::middleware(['checklogin'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('main');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/kalender-catering', [CateringOrderController::class, 'calendar'])->name('catering.calendar');
    Route::post('/kalender-catering', [CateringOrderController::class, 'store'])->name('catering.calendar.store');
    Route::post('/kalender-catering/{order}/update', [CateringOrderController::class, 'update'])->name('catering.calendar.update');
    Route::post('/kalender-catering/{order}/delete', [CateringOrderController::class, 'destroy'])->name('catering.calendar.destroy');
    Route::get('/kalender-catering/{order}/print', [CateringOrderController::class, 'print'])->name('catering.print');

    Route::get('/master-customer', [ModuleController::class, 'customers'])->name('customers.index');
    Route::post('/master-customer', [ModuleController::class, 'storeCustomer'])->name('customers.store');
    Route::get('/bahan-baku', [ModuleController::class, 'ingredients'])->name('ingredients.index');
    Route::post('/bahan-baku', [ModuleController::class, 'storeIngredient'])->name('ingredients.store');
    Route::post('/bahan-baku/{ingredient}/update', [ModuleController::class, 'updateIngredient'])->name('ingredients.update');
    Route::post('/bahan-baku/{ingredient}/delete', [ModuleController::class, 'destroyIngredient'])->name('ingredients.destroy');
    Route::get('/menu', [ModuleController::class, 'menuItems'])->name('menu-items.index');
    Route::post('/menu', [ModuleController::class, 'storeMenuItem'])->name('menu-items.store');
    Route::post('/menu/{menuItem}/update', [ModuleController::class, 'updateMenuItem'])->name('menu-items.update');
    Route::post('/menu/{menuItem}/delete', [ModuleController::class, 'destroyMenuItem'])->name('menu-items.destroy');
    Route::get('/meja', [ModuleController::class, 'tables'])->name('tables.index');
    Route::post('/meja', [ModuleController::class, 'storeTable'])->name('tables.store');
    Route::get('/meja/{table}/print', function (\App\Models\DiningTable $table) {
        return view('modules.tables-print', ['table' => $table, 'orderUrl' => route('restaurant-orders.public', $table->qr_token)]);
    })->name('tables.print');
    Route::get('/belanja-bahan-baku', [ModuleController::class, 'purchases'])->name('purchases.index');
    Route::post('/belanja-bahan-baku', [ModuleController::class, 'storePurchase'])->name('purchases.store');
    Route::get('/stok-opname', [ModuleController::class, 'stockOpnames'])->name('stock-opnames.index');
    Route::post('/stok-opname', [ModuleController::class, 'storeStockOpname'])->name('stock-opnames.store');
    Route::get('/laporan', [ModuleController::class, 'reports'])->name('reports.index');
    Route::get('/setting', [ModuleController::class, 'settings'])->name('settings.index');
    Route::post('/setting', [ModuleController::class, 'updateSettings'])->name('settings.update');
    Route::get('/transaksi-resto', [RestaurantOrderController::class, 'index'])->name('restaurant-orders.index');
    Route::post('/transaksi-resto', [RestaurantOrderController::class, 'store'])->name('restaurant-orders.store');
    Route::post('/transaksi-resto/{order}/update', [RestaurantOrderController::class, 'update'])->name('restaurant-orders.update');
    Route::post('/transaksi-resto/{order}/delete', [RestaurantOrderController::class, 'destroy'])->name('restaurant-orders.destroy');
    Route::post('/transaksi-resto/{order}/pay', [RestaurantOrderController::class, 'pay'])->name('restaurant-orders.pay');
    Route::get('/transaksi-resto/{order}/print', [RestaurantOrderController::class, 'print'])->name('restaurant-orders.print');
    Route::get('/display-dapur', [RestaurantOrderController::class, 'kitchen'])->name('kitchen.index');
    Route::post('/display-dapur/{item}/status', [RestaurantOrderController::class, 'updateKitchenStatus'])->name('kitchen.status');

    Route::get('/minor', [HomeController::class, 'minor'])->name("minor");

});
