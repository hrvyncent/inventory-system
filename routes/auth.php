<?php

use App\Http\Controllers\Authentication\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', fn() => view('login'))->name('login');
    Route::post('login', LoginController::class)->name('authenticate');
});