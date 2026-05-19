<?php

namespace App\Services\Rh;

use Illuminate\Support\Collection;

class RhClientMock implements RhClientInterface
{
    private array $users = [];

    public function __construct()
    {
        $this->users = [
            [
                'id' => 'RH-001',
                'name' => 'Fernando Basave Arce',
                'email' => 'fernando.basave@gptservices.com',
                'activo' => true,
                'puesto' => 'Gerente de Proyectos',
                'departamento' => 'Proyectos',
                'employeeId' => 'EMP-001',
            ],
            [
                'id' => 'RH-002',
                'name' => 'Guillermo Gutierrez Melo',
                'email' => 'guillermo.gutierrez@gptservices.com',
                'activo' => true,
                'puesto' => 'Director General',
                'departamento' => 'Dirección',
                'employeeId' => 'EMP-002',
            ],
            [
                'id' => 'RH-003',
                'name' => 'Denisse Ramirez',
                'email' => 'denisse@gptservices.com',
                'activo' => true,
                'puesto' => 'CFO',
                'departamento' => 'Finanzas',
                'employeeId' => 'EMP-003',
            ],
            [
                'id' => 'RH-004',
                'name' => 'Erick Daniel Morales Llerena',
                'email' => 'erick.morales@gptservices.com',
                'activo' => true,
                'puesto' => 'Coordinador QHSE',
                'departamento' => 'QHSE',
                'employeeId' => 'EMP-004',
            ],
        ];
    }

    public function searchByEmail(string $email): ?RhUser
    {
        foreach ($this->users as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                return $this->toDto($u);
            }
        }
        return null;
    }

    public function getById(string $userId): ?RhUser
    {
        foreach ($this->users as $u) {
            if ($u['id'] === $userId) {
                return $this->toDto($u);
            }
        }
        return null;
    }

    public function listAll(): Collection
    {
        return collect($this->users)->map(fn($u) => $this->toDto($u));
    }

    private function toDto(array $u): RhUser
    {
        return new RhUser(
            id:           $u['id'],
            name:         $u['name'],
            email:        $u['email'],
            activo:       $u['activo'] ?? true,
            puesto:       $u['puesto'] ?? null,
            departamento: $u['departamento'] ?? null,
            employeeId:   $u['employeeId'] ?? null,
        );
    }
}
