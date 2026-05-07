<?php

namespace App\Services\Rh;

use Illuminate\Support\Collection;

class RhUser
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $puesto = null,
        public readonly ?string $departamento = null,
        public readonly ?string $employeeId = null,
    ) {}
}

interface RhClientInterface
{
    public function searchByEmail(string $email): ?RhUser;
    public function getById(string $userId): ?RhUser;
    public function listAll(): Collection;
}
