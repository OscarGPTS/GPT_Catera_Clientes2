<?php

use App\Http\Controllers\Comercial\ClienteController;
use App\Livewire\Finanzas\FinanzasIndex;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Proyectos\AdjudicacionController;
use App\Http\Controllers\Proyectos\MinutaController;
use App\Http\Controllers\Proyectos\OportunidadController;
use App\Livewire\Chat\ChatDrawer;
use App\Livewire\Comercial\ClientesIndex;
use App\Livewire\Comercial\ClienteDetalle;
use App\Livewire\Configuracion\ConfiguracionIndex;
use App\Livewire\Ejecutivo\DashboardIndex;
use App\Livewire\Finanzas\CierresIndex;
use App\Livewire\NuevaOportunidad;
use App\Livewire\Proyectos\AdjudicacionForm;
use App\Livewire\Notificaciones\NotificationsIndex;
use App\Livewire\Proyectos\AsignacionesIndex;
use App\Livewire\Proyectos\BitacoraForm;
use App\Livewire\Proyectos\BomBoeIndex;
use App\Livewire\Proyectos\CotizacionEditor;
use App\Livewire\Proyectos\LibroProyectoEditor;
use App\Livewire\Proyectos\MinutaForm;
use App\Livewire\Proyectos\OportunidadDetalle;
use App\Livewire\Proyectos\OportunidadesIndex;
use App\Livewire\Proyectos\ProyectosIndex;
use App\Livewire\Proyectos\SuministrosIndex;
use App\Livewire\Proyectos\ViaticosList;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardIndex::class)->middleware(['auth', 'can:ver dashboard'])->name('dashboard');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Oportunidades
    Route::get('/oportunidades', OportunidadesIndex::class)->name('oportunidades.index')->can('ver oportunidades');
    Route::get('/oportunidades/nueva', NuevaOportunidad::class)->name('oportunidades.create')->can('crear oportunidad');
    Route::get('/oportunidades/{proyecto}', OportunidadDetalle::class)->name('oportunidades.show')->can('ver oportunidades');
    Route::post('/oportunidades/{proyecto}/aprobar', [OportunidadController::class, 'aprobar'])->name('oportunidades.aprobar')->can('aprobar cp');
    Route::post('/oportunidades/{proyecto}/rechazar', [OportunidadController::class, 'rechazar'])->name('oportunidades.rechazar')->can('aprobar cp');
    Route::post('/oportunidades/{proyecto}/asignar-equipo', [OportunidadController::class, 'asignarEquipo'])->name('oportunidades.asignar-equipo')->can('asignar cp');
    Route::post('/oportunidades/{proyecto}/cambiar-estado', [OportunidadController::class, 'cambiarEstado'])->name('oportunidades.cambiar-estado');

    // Proyectos en ejecución
    Route::get('/proyectos', ProyectosIndex::class)->name('proyectos.index')->can('ver proyectos');

    // Asignaciones (static route before {proyecto})
    Route::get('/proyectos/asignaciones', AsignacionesIndex::class)->name('proyectos.asignaciones')->can('ver asignaciones');

    // BOM/BOE (static route before {proyecto})
    Route::get('/proyectos/bom-boe', BomBoeIndex::class)->name('proyectos.bom-boe')->can('ver bom boe');

    // Suministros (static route before {proyecto})
    Route::get('/proyectos/suministros', SuministrosIndex::class)->name('proyectos.suministros')->can('ver suministros');

    // Proyecto detail (dynamic route AFTER static sub-routes)
    Route::get('/proyectos/{proyecto}', OportunidadDetalle::class)->name('proyectos.show')->can('ver proyectos');

    // Cotización
    Route::get('/proyectos/{proyecto}/cotizacion', CotizacionEditor::class)->name('proyectos.cotizacion')->can('ver cotizaciones');

    // Minuta de Entrega
    Route::get('/proyectos/{proyecto}/minuta', MinutaForm::class)->name('proyectos.minuta')->can('crear minuta');
    Route::post('/proyectos/{proyecto}/minuta', [MinutaController::class, 'store'])->name('proyectos.minuta.store')->can('crear minuta');
    Route::post('/proyectos/{proyecto}/minuta/firmar', [MinutaController::class, 'firmar'])->name('proyectos.minuta.firmar')->can('firmar minuta');

    // Adjudicación
    Route::get('/proyectos/{proyecto}/adjudicar', AdjudicacionForm::class)->name('proyectos.adjudicar')->can('adjudicar proyecto');
    Route::post('/proyectos/{proyecto}/adjudicar', [AdjudicacionController::class, 'adjudicar'])->name('proyectos.adjudicar.store')->can('adjudicar proyecto');
    Route::post('/proyectos/{proyecto}/firmar-adjudicacion', [AdjudicacionController::class, 'firmar'])->name('proyectos.adjudicar.firmar')->can('adjudicar proyecto');

    // Libro de Proyecto
    Route::get('/proyectos/{id}/libro', LibroProyectoEditor::class)->name('proyectos.libro')->can('ver libro proyecto');

    // Clientes
    Route::get('/clientes', ClientesIndex::class)->name('clientes.index')->can('ver proyectos');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}', ClienteDetalle::class)->name('clientes.show');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::post('/clientes/{cliente}/contacto', [ClienteController::class, 'addContacto'])->name('clientes.add-contacto');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

    // Bitácora
    Route::get('/bitacora', BitacoraForm::class)->name('bitacora')->can('ver bitacora');

    // Viáticos
    Route::get('/viaticos', ViaticosList::class)->name('viaticos')->can('solicitar viaticos');

    // Finanzas
    Route::get('/finanzas', FinanzasIndex::class)->name('finanzas.index')->middleware('finanzas');
    Route::get('/finanzas/cierres', CierresIndex::class)->name('finanzas.cierres')->middleware('finanzas');

    // Ejecutivo
    Route::get('/ejecutivo', DashboardIndex::class)->name('ejecutivo.dashboard')->can('ver vista ejecutiva');

    // Perfil
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::get('/perfil/mi-asignacion', [PerfilController::class, 'miAsignacion'])->name('perfil.asignacion');

    // Configuración
    Route::get('/configuracion', ConfiguracionIndex::class)->name('configuracion.index');

    // Notificaciones
    Route::get('/notificaciones', NotificationsIndex::class)->name('notificaciones.index');

    // Chat
    Route::get('/chat', ChatDrawer::class)->middleware('auth')->name('chat.index');

    
});

require __DIR__.'/admin.php';