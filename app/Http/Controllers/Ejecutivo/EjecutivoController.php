<?php

namespace App\Http\Controllers\Ejecutivo;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use Illuminate\Http\Request;

class EjecutivoController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorize('ver vista ejecutiva');

        $ano = $request->input('ano', now()->year);

        $query = Proyecto::with(['cliente', 'sublinea', 'gerenteProyectos']);

        $pipeline = $query->get();
        $pipelineTotal = $pipeline->count();

        $estadosAdjudicados = ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion', 'en_cierre', 'cerrado'];
        $adjudicadosCount = $pipeline->whereIn('estado', $estadosAdjudicados)->count();

        $hitRateConteo = $pipelineTotal > 0
            ? round(($adjudicadosCount / $pipelineTotal) * 100, 1)
            : 0;

        $concentracionSedena = $pipelineTotal > 0
            ? round(($pipeline->where('sector', 'Defensa')->count() / $pipelineTotal) * 100, 1)
            : 0;

        $porSublinea = $pipeline->groupBy(fn($p) => $p->sublinea?->nombre ?? 'Sin línea')->map->count()->sortDesc();
        $porEstado = $pipeline->groupBy('estado')->map->count();
        $porCliente = $pipeline->groupBy(fn($p) => $p->cliente?->razon_social ?? 'Sin cliente')->map->count()->sortDesc();

        $oportunidadesAtencion = Proyecto::whereIn('estado', ['en_revision', 'cotizando', 'presentado'])
            ->where('updated_at', '<', now()->subDays(7))
            ->with(['cliente', 'sublinea', 'gerenteProyectos'])
            ->orderBy('updated_at')
            ->take(5)
            ->get()
            ->map(fn($p) => (object) [
                'id' => $p->id,
                'cp' => $p->cp_numero,
                'ref_tecnica' => $p->tech_reference,
                'cliente' => $p->cliente,
                'monto_estimado' => $p->cotizaciones->max('precio_venta_final') ?? 0,
                'probabilidad' => match($p->estado) { 'cotizando' => 40, 'presentado' => 60, 'en_revision' => 20, default => 30 },
                'dias_sin_actividad' => $p->updated_at->diffInDays(now()),
                'lider' => $p->gerenteProyectos ?? $p->directorDn,
                'accion_sugerida' => match($p->estado) { 'cotizando' => 'Cotizar', 'presentado' => 'Presentar', default => 'Dar seguimiento' },
            ]);

        $gerentes = $pipeline->whereNotNull('gerente_proyectos_id')->groupBy('gerente_proyectos_id');
        $cargaEquipo = [
            'avg' => $gerentes->count() > 0 ? round($gerentes->map->count()->avg(), 1) : 0,
            'sobrecarga' => $gerentes->map->count()->filter(fn($c) => $c > 6)->count(),
            'total' => $gerentes->count(),
        ];

        $dossiersRiesgo = Proyecto::where('estado', 'en_ejecucion')
            ->whereDoesntHave('cotizaciones')
            ->count();

        $postMortems = Proyecto::whereIn('estado', ['cerrado', 'cancelado', 'perdido'])->count();

        $clientes = Cliente::where('activo', true)->orderBy('razon_social')->get();
        $sublineas = Sublinea::all();

        $nombre_usuario = auth()->user()->name ?? 'Usuario';
        $quarter_label = 'Q' . ceil(now()->month / 3) . ' ' . now()->year;

        return view('ejecutivo.dashboard', compact(
            'pipeline', 'pipelineTotal', 'adjudicadosCount',
            'hitRateConteo', 'concentracionSedena', 'porSublinea',
            'porEstado', 'porCliente', 'oportunidadesAtencion',
            'cargaEquipo', 'dossiersRiesgo', 'postMortems',
            'clientes', 'sublineas', 'nombre_usuario', 'quarter_label', 'ano',
        ));
    }
}