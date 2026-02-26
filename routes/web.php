<?php

use Illuminate\Support\Facades\Route;
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

    Route::get('/', [HomeController::class, 'index'])->name("main");

    Route::get('/minor', [HomeController::class, 'minor'])->name("minor");

});
