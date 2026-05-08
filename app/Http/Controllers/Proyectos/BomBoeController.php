<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\BomBoeItem;
use Illuminate\Http\Request;

class BomBoeController extends Controller
{
    public function index(Request $request)
    {
        $proyectos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre', 'adjudicado_firmado'])
            ->with(['cliente', 'sublinea'])
            ->orderBy('cp_numero')
            ->paginate(20);

        $selectedProyecto = null;
        $items = collect();

        if ($request->filled('proyecto_id')) {
            $selectedProyecto = Proyecto::findOrFail($request->input('proyecto_id'));
            $items = BomBoeItem::where('proyecto_id', $selectedProyecto->id)
                ->with('responsable')
                ->orderBy('tipo')
                ->orderBy('descripcion')
                ->paginate(50, ['*'], 'items');
        }

        return view('proyectos.bom-boe', compact('proyectos', 'selectedProyecto', 'items'));
    }

    public function store(Request $request)
    {
        $this->authorize('editar bom boe');

        $validated = $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id',
            'tipo' => 'required|in:BOM,BOE',
            'descripcion' => 'required|string|max:500',
            'cantidad' => 'required|numeric|min:0',
            'unidad' => 'required|string|max:50',
            'status' => 'required|in:en_almacen,por_afilar,por_fabricar,por_comprar,en_transito,entregado',
            'fecha_requerida' => 'nullable|date',
        ]);

        BomBoeItem::create([
            ...$validated,
            'responsable_id' => auth()->id(),
        ]);

        return back()->with('success', 'Item agregado correctamente.');
    }

    public function update(Request $request, BomBoeItem $item)
    {
        $this->authorize('editar bom boe');

        $validated = $request->validate([
            'status' => 'sometimes|in:en_almacen,por_afilar,por_fabricar,por_comprar,en_transito,entregado',
            'descripcion' => 'sometimes|string|max:500',
            'cantidad' => 'sometimes|numeric|min:0',
        ]);

        $item->update($validated);

        return back()->with('success', 'Item actualizado.');
    }

    public function destroy(BomBoeItem $item)
    {
        $this->authorize('editar bom boe');
        $item->delete();

        return back()->with('success', 'Item eliminado.');
    }
}