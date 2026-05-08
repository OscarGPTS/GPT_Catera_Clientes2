<?php

use App\Http\Controllers\Admin\RhMappingController;
use App\Http\Controllers\Admin\RolesPermisosController;
use App\Http\Controllers\Admin\SocioController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:ver admin usuarios'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'invite'])->name('usuarios.invite');
    Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::put('/usuarios/{usuario}/role', [UsuarioController::class, 'updateRole'])->name('usuarios.update-role');
    Route::post('/usuarios/{usuario}/suspend', [UsuarioController::class, 'suspend'])->name('usuarios.suspend');
    Route::post('/usuarios/{usuario}/activate', [UsuarioController::class, 'activate'])->name('usuarios.activate');
    Route::post('/usuarios/sync-rh', [UsuarioController::class, 'syncRh'])->name('usuarios.sync-rh');

    Route::middleware('can:ver admin socios')->group(function () {
        Route::get('/socios', [SocioController::class, 'index'])->name('socios.index');
        Route::post('/socios/allowlist', [SocioController::class, 'addAllowlist'])->name('socios.add-allowlist');
        Route::delete('/socios/allowlist/{socio}', [SocioController::class, 'removeAllowlist'])->name('socios.remove-allowlist');
        Route::post('/socios/{usuario}/toggle-override', [SocioController::class, 'toggleOverride'])->name('socios.toggle-override');
    });

    Route::middleware('can:gestionar rh mapping')->group(function () {
        Route::get('/rh-mapping', [RhMappingController::class, 'index'])->name('rh-mapping.index');
        Route::post('/rh-mapping', [RhMappingController::class, 'store'])->name('rh-mapping.store');
        Route::put('/rh-mapping/{mapping}', [RhMappingController::class, 'update'])->name('rh-mapping.update');
        Route::delete('/rh-mapping/{mapping}', [RhMappingController::class, 'destroy'])->name('rh-mapping.destroy');
        Route::post('/rh-mapping/reorder', [RhMappingController::class, 'reorder'])->name('rh-mapping.reorder');
    });

    Route::middleware('can:ver roles permisos')->group(function () {
        Route::get('/roles-permisos', [RolesPermisosController::class, 'index'])->name('roles-permisos.index');
    });
});