<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\ContactoCliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClienteController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cliente::class);

        $query = Cliente::with('contactos');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('razon_social', 'ilike', "%{$buscar}%")
                    ->orWhere('alias_3letras', 'ilike', "%{$buscar}%")
                    ->orWhere('rfc', 'ilike', "%{$buscar}%");
            });
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->input('sector'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->where('activo', true);
        }

        $clientes = $query->orderBy('razon_social')->paginate(25)->withQueryString();

        $sectores = Cliente::where('activo', true)
            ->whereNotNull('sector')
            ->distinct()
            ->pluck('sector')
            ->sort()
            ->values();

        return view('comercial.clientes', compact('clientes', 'sectores'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Cliente::class);

        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'alias_3letras' => 'required|string|max:5|unique:clientes,alias_3letras',
            'rfc' => 'nullable|string|max:13|unique:clientes,rfc',
            'sector' => 'nullable|string|max:100',
            'segmento' => 'nullable|string|max:100',
        ]);

        $cliente = Cliente::create($validated);

        return back()->with('success', "Cliente {$cliente->razon_social} creado correctamente.");
    }

    public function show(Cliente $cliente)
    {
        $this->authorize('view', $cliente);

        $cliente->load(['contactos', 'proyectos' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('comercial.cliente-detalle', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('update', $cliente);

        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'alias_3letras' => ['required', 'string', 'max:5', Rule::unique('clientes', 'alias_3letras')->ignore($cliente->id)],
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