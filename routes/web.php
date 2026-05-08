<?php

use App\Http\Controllers\Comercial\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\Ejecutivo\EjecutivoController;
use App\Http\Controllers\Finanzas\CierreController;
use App\Http\Controllers\Finanzas\FinanzaController;
use App\Livewire\Finanzas\CierresIndex;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Proyectos\AdjudicacionController;
use App\Http\Controllers\Proyectos\AsignacionController;
use App\Livewire\Proyectos\AsignacionesIndex;
use App\Http\Controllers\Proyectos\BomBoeController;
use App\Http\Controllers\Proyectos\MinutaController;
use App\Http\Controllers\Proyectos\OportunidadController;
use App\Http\Controllers\Proyectos\ProyectoController;
use App\Http\Controllers\Proyectos\SuministroController;
use App\Livewire\NuevaOportunidad;
use App\Livewire\Proyectos\BitacoraForm;
use App\Livewire\Proyectos\CotizacionEditor;
use App\Livewire\Proyectos\LibroProyectoEditor;
use App\Livewire\Proyectos\ViaticosList;
use Illuminate\Support\Facades\Route;

Route::get('/', [EjecutivoController::class, 'dashboard'])->middleware(['auth', 'can:ver dashboard'])->name('dashboard');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Oportunidades
    Route::get('/oportunidades', [OportunidadController::class, 'index'])->name('oportunidades.index')->can('ver oportunidades');
    Route::get('/oportunidades/nueva', NuevaOportunidad::class)->name('oportunidades.create')->can('crear oportunidad');
    Route::get('/oportunidades/{proyecto}', [OportunidadController::class, 'show'])->name('oportunidades.show')->can('ver oportunidades');
    Route::post('/oportunidades/{proyecto}/aprobar', [OportunidadController::class, 'aprobar'])->name('oportunidades.aprobar')->can('aprobar cp');
    Route::post('/oportunidades/{proyecto}/rechazar', [OportunidadController::class, 'rechazar'])->name('oportunidades.rechazar')->can('aprobar cp');
    Route::post('/oportunidades/{proyecto}/asignar-equipo', [OportunidadController::class, 'asignarEquipo'])->name('oportunidades.asignar-equipo')->can('asignar cp');
    Route::post('/oportunidades/{proyecto}/cambiar-estado', [OportunidadController::class, 'cambiarEstado'])->name('oportunidades.cambiar-estado');

    // Proyectos en ejecución
    Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index')->can('ver proyectos');
    Route::get('/proyectos/{proyecto}', [ProyectoController::class, 'show'])->name('proyectos.show')->can('ver proyectos');

    // Asignaciones
    Route::get('/proyectos/asignaciones', AsignacionesIndex::class)->name('proyectos.asignaciones')->can('ver asignaciones');

    // BOM/BOE
    Route::get('/proyectos/bom-boe', [BomBoeController::class, 'index'])->name('proyectos.bom-boe')->can('ver bom boe');
    Route::post('/proyectos/bom-boe', [BomBoeController::class, 'store'])->name('proyectos.bom-boe.store')->can('editar bom boe');
    Route::put('/proyectos/bom-boe/{item}', [BomBoeController::class, 'update'])->name('proyectos.bom-boe.update')->can('editar bom boe');
    Route::delete('/proyectos/bom-boe/{item}', [BomBoeController::class, 'destroy'])->name('proyectos.bom-boe.destroy')->can('editar bom boe');

    // Suministros
    Route::get('/proyectos/suministros', [SuministroController::class, 'index'])->name('proyectos.suministros')->can('ver suministros');

    // Cotización
    Route::get('/proyectos/{proyecto}/cotizacion', CotizacionEditor::class)->name('proyectos.cotizacion')->can('ver cotizaciones');

    // Minuta de Entrega
    Route::get('/proyectos/{proyecto}/minuta', [MinutaController::class, 'show'])->name('proyectos.minuta')->can('crear minuta');
    Route::post('/proyectos/{proyecto}/minuta', [MinutaController::class, 'store'])->name('proyectos.minuta.store')->can('crear minuta');
    Route::post('/proyectos/{proyecto}/minuta/firmar', [MinutaController::class, 'firmar'])->name('proyectos.minuta.firmar')->can('firmar minuta');

    // Adjudicación
    Route::get('/proyectos/{proyecto}/adjudicar', [AdjudicacionController::class, 'show'])->name('proyectos.adjudicar')->can('adjudicar proyecto');
    Route::post('/proyectos/{proyecto}/adjudicar', [AdjudicacionController::class, 'adjudicar'])->name('proyectos.adjudicar.store')->can('adjudicar proyecto');
    Route::post('/proyectos/{proyecto}/firmar-adjudicacion', [AdjudicacionController::class, 'firmar'])->name('proyectos.adjudicar.firmar')->can('adjudicar proyecto');

    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index')->can('ver proyectos');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::post('/clientes/{cliente}/contacto', [ClienteController::class, 'addContacto'])->name('clientes.add-contacto');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    // Libro de Proyecto
    Route::get('/proyectos/{id}/libro', LibroProyectoEditor::class)->name('proyectos.libro')->can('ver libro proyecto');

    // Bitácora
    Route::get('/bitacora', BitacoraForm::class)->name('bitacora')->can('ver bitacora');

    // Viáticos
    Route::get('/viaticos', ViaticosList::class)->name('viaticos')->can('solicitar viaticos');

    // Finanzas
    Route::get('/finanzas', [FinanzaController::class, 'index'])->name('finanzas.index')->middleware('finanzas');
    Route::get('/finanzas/cierres', CierresIndex::class)->name('finanzas.cierres')->middleware('finanzas');

    // Ejecutivo
    Route::get('/ejecutivo', [EjecutivoController::class, 'dashboard'])->name('ejecutivo.dashboard')->can('ver vista ejecutiva');

    // Perfil
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::get('/perfil/mi-asignacion', [PerfilController::class, 'miAsignacion'])->name('perfil.asignacion');

    // Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');

    // Auth link provider
    Route::middleware('auth')->group(function () {
        Route::get('/vincular-metodo', [App\Http\Controllers\Auth\LinkProviderController::class, 'show'])->name('auth.link-provider.show');
        Route::post('/vincular-metodo/{provider}', [App\Http\Controllers\Auth\LinkProviderController::class, 'redirect'])->name('auth.link-provider.redirect');
        Route::get('/vincular-metodo/{provider}/callback', [App\Http\Controllers\Auth\LinkProviderController::class, 'callback'])->name('auth.link-provider.callback');
        Route::delete('/vincular-metodo/{provider}', [App\Http\Controllers\Auth\LinkProviderController::class, 'unlink'])->name('auth.link-provider.unlink');
    });
});

require __DIR__.'/admin.php';