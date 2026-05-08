<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\CotizacionPartida;
use App\Services\Cotizaciones\CalculadoraCoss;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function show(Proyecto $proyecto)
    {
        $proyecto->load(['cliente', 'sublinea', 'cotizaciones.partidas']);

        $cotizaciones = $proyecto->cotizaciones()->orderBy('version', 'desc')->get();
        $cotizacionActiva = $cotizaciones->first();

        return view('proyectos.cotizacion-editor', [
            'proyectoId' => $proyecto->id,
            'proyecto' => $proyecto,
            'cotizaciones' => $cotizaciones,
            'cotizacionActiva' => $cotizacionActiva,
        ]);
    }

    public function store(Request $request, Proyecto $proyecto)
    {
        $this->authorize('crear cotizacion');

        $validated = $request->validate([
            'partidas' => 'required|array|min:1',
            'partidas.*.descripcion' => 'required|string|max:500',
            'partidas.*.cantidad' => 'required|numeric|min:0',
            'partidas.*.unidad' => 'required|string|max:50',
            'partidas.*.costo_unitario' => 'required|numeric|min:0',
            'factor_indirectos' => 'required|numeric|min:0',
            'factor_admin' => 'required|numeric|min:0',
            'factor_utilidad' => 'required|numeric|min:0',
            'moneda' => 'required|in:USD,MXN',
        ]);

        $ultimaVersion = $proyecto->cotizaciones()->max('version') ?? 0;
        $nuevaVersion = $ultimaVersion + 1;

        $partidasData = collect($validated['partidas'])->map(fn($p, $i) => [
            'numero_partida' => $i + 1,
            'descripcion' => $p['descripcion'],
            'cantidad' => $p['cantidad'],
            'unidad' => $p['unidad'],
            'costo_unitario' => $p['costo_unitario'],
            'costo_total' => $p['cantidad'] * $p['costo_unitario'],
        ]);

        $costoDirecto = $partidasData->sum('costo_total');

        $calculadora = new CalculadoraCoss();
        $resultado = $calculadora->calcularDesdePartidas(
            $partidasData->toArray(),
            [
                'indirectos' => $validated['factor_indirectos'],
                'admin' => $validated['factor_admin'],
                'utilidad' => $validated['factor_utilidad'],
            ],
        );

        $cotizacion = Cotizacion::create([
            'proyecto_id' => $proyecto->id,
            'version' => $nuevaVersion,
            'costo_directo' => $resultado->costoDirecto,
            'factor_indirectos' => $validated['factor_indirectos'],
            'factor_admin' => $validated['factor_admin'],
            'factor_utilidad' => $validated['factor_utilidad'],
            'precio_venta_calculado' => $resultado->precioVenta,
            'precio_venta_final' => $resultado->precioVenta,
            'moneda' => $validated['moneda'],
            'status' => 'borrador',
            'generado_por' => auth()->id(),
        ]);

        foreach ($partidasData as $partida) {
            CotizacionPartida::create([
                'cotizacion_id' => $cotizacion->id,
                ...$partida,
            ]);
        }

        $proyecto->eventos()->create([
            'tipo' => 'cotizacion_creada',
            'user_id' => auth()->id(),
            'comentario' => "Cotización v{$nuevaVersion} creada. Precio venta: $" . number_format($resultado->precioVenta, 2),
        ]);

        return redirect()->route('proyectos.cotizacion', $proyecto)
            ->with('success', "Cotización v{$nuevaVersion} creada correctamente.");
    }
}