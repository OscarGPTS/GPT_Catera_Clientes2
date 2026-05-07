<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');

// Oportunidades
Route::middleware(['auth'])->group(function () {
    Route::get('/oportunidades', function () {
        $proyectos = App\Models\Proyectos\Proyecto::with(['cliente', 'sublinea', 'gerenteProyectos'])
            ->latest()->paginate(20);
        return view('proyectos.oportunidades', compact('proyectos'));
    })->name('oportunidades.index');

    Route::get('/oportunidades/nueva', \App\Livewire\NuevaOportunidad::class)->name('oportunidades.create');

    // Proyectos
    Route::get('/proyectos', function () {
        $proyectos = App\Models\Proyectos\Proyecto::with(['cliente', 'sublinea'])
            ->whereIn('estado', ['adjudicado_firmado', 'en_ejecucion', 'en_cierre'])
            ->latest()->paginate(20);
        return view('proyectos.index', compact('proyectos'));
    })->name('proyectos.index');

    Route::get('/proyectos/asignaciones', function () {
        return view('proyectos.asignaciones');
    })->name('proyectos.asignaciones');

    Route::get('/proyectos/bom-boe', function () {
        return view('proyectos.bom-boe');
    })->name('proyectos.bom-boe');

    Route::get('/proyectos/suministros', function () {
        return view('proyectos.suministros');
    })->name('proyectos.suministros');

    // Cotización
    Route::get('/proyectos/{id}/cotizacion', function ($id) {
        return view('proyectos.cotizacion-editor', ['proyectoId' => $id]);
    })->name('proyectos.cotizacion');

    // Minuta de Entrega
    Route::get('/proyectos/{id}/minuta', function ($id) {
        return view('proyectos.minuta-entrega', ['proyectoId' => $id]);
    })->name('proyectos.minuta');

    // Libro de Proyecto
    Route::get('/proyectos/{id}/libro', function ($id) {
        return view('proyectos.libro-proyecto', ['proyectoId' => $id]);
    })->name('proyectos.libro');

    // Bitácora
    Route::get('/bitacora', function () {
        return view('proyectos.bitacora');
    })->name('bitacora');
});

require __DIR__.'/auth.php';
