<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
