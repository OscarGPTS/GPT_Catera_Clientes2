<?php

use App\Http\Controllers\Auth\Auth0Controller;
use App\Http\Controllers\Auth\LinkProviderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Google OAuth — callback path registered in Google Cloud Console
Route::get('/login/google/callback', fn (SocialiteController $c) => $c->callback('google'))->name('google.callback');

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/auth0/redirect', [Auth0Controller::class, 'redirect'])->name('auth0.redirect');
    Route::get('/auth0/callback', [Auth0Controller::class, 'callback'])->name('auth0.callback');

    // Shortcut: /auth/google → redirect to Google OAuth
    Route::get('/google', fn (SocialiteController $c) => $c->redirect('google'))->name('google');

    Route::get('/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->whereIn('provider', ['google', 'microsoft', 'apple'])
        ->name('socialite.redirect');
    Route::get('/{provider}/callback', [SocialiteController::class, 'callback'])
        ->whereIn('provider', ['google', 'microsoft', 'apple'])
        ->name('socialite.callback');

    Route::middleware('auth')->group(function () {
        Route::get('/link-provider', [LinkProviderController::class, 'show'])->name('link-provider.show');
        Route::post('/link-provider/{provider}', [LinkProviderController::class, 'redirect'])->name('link-provider.redirect');
        Route::get('/link-provider/{provider}/callback', [LinkProviderController::class, 'callback'])->name('link-provider.callback');
        Route::delete('/link-provider/{provider}', [LinkProviderController::class, 'unlink'])->name('link-provider.unlink');
    });
});