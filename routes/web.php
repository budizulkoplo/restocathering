<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CutiDokterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

/*
LOGIN
*/
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

/*
PROTECTED
*/
Route::middleware(['checklogin'])->group(function () {

    Route::get('/', [CutiDokterController::class, 'dashboard'])->name('main');
    Route::get('/dashboard', [CutiDokterController::class, 'dashboard'])->name('cuti.dashboard');
    Route::get('/kalender-cuti', [CutiDokterController::class, 'calendar'])->name('cuti.calendar');
    Route::post('/kalender-cuti', [CutiDokterController::class, 'store'])->name('cuti.calendar.store');

    Route::get('/minor', [HomeController::class, 'minor'])->name("minor");

});
