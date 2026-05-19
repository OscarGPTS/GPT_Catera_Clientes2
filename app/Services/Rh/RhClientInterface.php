<?php

namespace App\Services\Rh;

use Illuminate\Support\Collection;

class RhUser
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $email,
        public readonly bool $activo = true,
        public readonly ?string $puesto = null,
        public readonly ?string $departamento = null,
        public readonly ?string $area = null,
        public readonly ?string $employeeId = null,
        public readonly ?string $razonSocial = null,
        public readonly ?string $jefeDirectoEmail = null,
    ) {}
}

/**
 * Excepción que indica que la API de RH no respondió o respondió con error
 * de servidor. Permite distinguir "no encontrado" (usuario inválido)
 * de "API caída" (fail-open).
 */
class RhUnavailableException extends \RuntimeException {}

interface RhClientInterface
{
    public function searchByEmail(string $email): ?RhUser;
    public function getById(string $userId): ?RhUser;
    public function listAll(): Collection;
}
