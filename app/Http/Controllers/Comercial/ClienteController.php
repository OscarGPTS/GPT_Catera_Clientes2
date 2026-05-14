<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClienteController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $this->authorize('create', Cliente::class);

        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'alias' => 'required|string|max:30|unique:clientes,alias',
            'rfc' => 'nullable|string|max:13|unique:clientes,rfc',
            'sector' => 'nullable|string|max:100',
            'segmento' => 'nullable|string|max:100',
        ]);

        $cliente = Cliente::create($validated);

        return back()->with('success', "Cliente {$cliente->razon_social} creado correctamente.");
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'alias' => ['required', 'string', 'max:30', Rule::unique('clientes', 'alias')->ignore($cliente->id)],
            'rfc' => ['nullable', 'string', 'max:13', Rule::unique('clientes', 'rfc')->ignore($cliente->id)],
            'sector' => 'nullable|string|max:100',
            'segmento' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ]);

        $cliente->update($validated);

        return back()->with('success', "Cliente {$cliente->razon_social} actualizado correctamente.");
    }

    public function addContacto(Request $request, Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'principal' => 'boolean',
        ]);

        if (!empty($validated['principal'])) {
            $cliente->contactos()->update(['principal' => false]);
        }

        $cliente->contactos()->create($validated);

        return back()->with('success', 'Contacto agregado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $this->authorize('delete', $cliente);

        $cliente->update(['activo' => false]);

        return redirect()->route('clientes.index')->with('success', "Cliente {$cliente->razon_social} desactivado.");
    }
}