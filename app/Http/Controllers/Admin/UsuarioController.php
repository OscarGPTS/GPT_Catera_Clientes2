<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct(
        private AuthOrchestrator $orchestrator,
    ) {}

    public function index(Request $request)
    {
        $query = User::with(['roles', 'authProviders']);

        if ($search = $request->input('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('puesto', 'ilike', "%{$search}%");
            });
        }

        if ($rol = $request->input('rol')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $rol));
        }

        if ($estado = $request->input('estado')) {
            $query->where('status', $estado);
        }

        if ($departamento = $request->input('departamento')) {
            $query->where('departamento', $departamento);
        }

        $usuarios = $query->orderBy('name')->paginate(25)->withQueryString();

        $totalActivos = User::where('status', 'active')->count();
        $totalInvitados = User::where('status', 'invited')->count();
        $totalSuspendidos = User::where('status', 'suspended')->count();
        $departamentos = User::whereNotNull('departamento')->distinct()->pluck('departamento')->sort();

        return view('admin.usuarios', compact(
            'usuarios', 'totalActivos', 'totalInvitados', 'totalSuspendidos', 'departamentos',
        ));
    }

    public function show(User $usuario)
    {
        $usuario->load(['roles', 'authProviders']);

        return response()->json($usuario);
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'status' => ['sometimes', Rule::in(['active', 'suspended'])],
        ]);

        $usuario->update($validated);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function updateRole(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $usuario->syncRoles([$validated['role']]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function invite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
            'departamento' => 'nullable|string|max:255',
        ]);

        try {
            $user = $this->orchestrator->inviteExternalUser(
                $validated['email'],
                $validated['role'],
                $validated['departamento'] ?? null,
                $validated['name'] ?? null,
            );

            return back()->with('success', "Invitación enviada a {$user->email}.");
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function suspend(User $usuario)
    {
        $usuario->update(['status' => 'suspended']);

        return back()->with('success', 'Usuario suspendido.');
    }

    public function activate(User $usuario)
    {
        $usuario->update(['status' => 'active']);

        return back()->with('success', 'Usuario activado.');
    }

    public function syncRh()
    {
        $rhClient = app(\App\Services\Rh\RhClientInterface::class);
        $rhUsers = $rhClient->listAll();
        $synced = 0;

        foreach ($rhUsers as $rhUser) {
            $user = User::where('email', $rhUser->email)->first();
            if ($user) {
                $user->update([
                    'puesto' => $rhUser->puesto,
                    'departamento' => $rhUser->departamento,
                    'employee_id' => $rhUser->employeeId,
                ]);
                $synced++;
            }
        }

        return back()->with('success', "{$synced} usuarios sincronizados con RH.");
    }
}