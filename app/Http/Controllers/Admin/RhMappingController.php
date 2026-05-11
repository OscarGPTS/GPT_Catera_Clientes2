<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RhRoleMapping;
use App\Models\User;
use Illuminate\Http\Request;

class RhMappingController extends Controller
{
    public function index()
    {
        return view('admin.rh-mapping');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'puesto_rh' => 'required|string|max:255',
            'rol_sistema' => 'required|string|exists:roles,name',
            'prioridad' => 'required|integer|min:0|max:100',
            'departamento_filter' => 'nullable|string|max:255',
        ]);

        RhRoleMapping::create([
            ...$validated,
            'activo' => true,
        ]);

        return back()->with('success', 'Regla de mapeo creada correctamente.');
    }

    public function update(Request $request, RhRoleMapping $mapping)
    {
        $validated = $request->validate([
            'puesto_rh' => 'sometimes|string|max:255',
            'rol_sistema' => 'sometimes|string|exists:roles,name',
            'prioridad' => 'sometimes|integer|min:0|max:100',
            'departamento_filter' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        $mapping->update($validated);

        return back()->with('success', 'Regla actualizada correctamente.');
    }

    public function destroy(RhRoleMapping $mapping)
    {
        $mapping->delete();

        return back()->with('success', 'Regla eliminada.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:rh_role_mapping,id',
            'order.*.prioridad' => 'required|integer',
        ]);

        foreach ($validated['order'] as $item) {
            RhRoleMapping::where('id', $item['id'])->update(['prioridad' => $item['prioridad']]);
        }

        return response()->json(['success' => true]);
    }
}