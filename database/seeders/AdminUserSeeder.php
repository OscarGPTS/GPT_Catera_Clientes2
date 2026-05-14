<?php

namespace Database\Seeders;

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Super-admin (ochavez) ──────────────────────────────────────────
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'ochavez@gptservices.com'],
            [
                'name'         => 'O. Chavez',
                'password'     => bcrypt('password'),
                'departamento' => 'Administración',
                'puesto'       => 'Administrador',
                'status'       => 'active',
            ]
        );

        if (! $admin->authProviders()->where('provider', 'email_password')->exists()) {
            AuthProvider::create([
                'user_id'       => $admin->id,
                'provider'      => 'email_password',
                'password_hash' => bcrypt('password'),
                'is_primary'    => true,
            ]);
        }

        $admin->syncRoles(['super_admin']);
        $this->command->info("Usuario {$admin->email} configurado con rol super_admin.");

        // ── 2. Usuario sistema: Sin Responsable (fallback de importación) ─────
        $sinResponsable = User::firstOrCreate(
            ['email' => 'sin.responsable@system.gptservices.com'],
            [
                'name'         => 'Sin Responsable',
                'password'     => bcrypt(\Illuminate\Support\Str::random(40)),
                'departamento' => 'Sistema',
                'puesto'       => 'Usuario Sistema',
                'status'       => 'suspended',
            ]
        );

        // No se asigna rol — es solo un placeholder para la FK
        $this->command->info("Usuario sistema '{$sinResponsable->name}' (id={$sinResponsable->id}) listo.");
    }
}
