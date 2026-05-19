<?php

namespace App\Services\Rh;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente HTTP contra el servicio RH oficial:
 *   GET  {base}/users
 *   POST {base}/users/buscar-por-email   { email: string }
 *   GET  {base}/users/{userId}
 *
 * Convenciones de la API observadas:
 *   200 + {success:true,  data: {...}}   → usuario encontrado
 *   404 + {success:false, message: "..."}→ no encontrado (devolvemos null)
 *   5xx / timeout / red                  → lanzamos RhUnavailableException
 *
 * El cache de búsquedas por email (5 min) reduce el tráfico y la latencia
 * del login. Se invalida automáticamente por TTL.
 */
class RhClientHttp implements RhClientInterface
{
    private const CACHE_TTL_SECONDS = 300; // 5 minutos
    private const REQUEST_TIMEOUT = 5;     // segundos
    private const CONNECT_TIMEOUT = 3;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
    ) {}

    public function searchByEmail(string $email): ?RhUser
    {
        $normalized = mb_strtolower(trim($email));
        $cacheKey = 'rh:email:' . sha1($normalized);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            // Convención: ['miss'] como sentinel para "ya buscado, no existe"
            return $cached === 'miss' ? null : $this->mapPayload($cached);
        }

        $response = $this->client()->post('/users/buscar-por-email', [
            'email' => $normalized,
        ]);

        if ($response->status() === 404) {
            Cache::put($cacheKey, 'miss', self::CACHE_TTL_SECONDS);
            return null;
        }

        if (! $response->successful()) {
            throw new RhUnavailableException(
                "RH API respondió con HTTP {$response->status()} para searchByEmail({$email})"
            );
        }

        $payload = $response->json('data');
        if (! is_array($payload)) {
            Cache::put($cacheKey, 'miss', self::CACHE_TTL_SECONDS);
            return null;
        }

        Cache::put($cacheKey, $payload, self::CACHE_TTL_SECONDS);
        return $this->mapPayload($payload);
    }

    public function getById(string $userId): ?RhUser
    {
        $response = $this->client()->get("/users/{$userId}");

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            throw new RhUnavailableException(
                "RH API respondió con HTTP {$response->status()} para getById({$userId})"
            );
        }

        $payload = $response->json('data');
        return is_array($payload) ? $this->mapPayload($payload) : null;
    }

    public function listAll(): Collection
    {
        $response = $this->client()->get('/users');

        if (! $response->successful()) {
            throw new RhUnavailableException(
                "RH API respondió con HTTP {$response->status()} para listAll"
            );
        }

        $rows = $response->json('data') ?? [];
        return collect($rows)->map(fn($r) => $this->mapPayload($r));
    }

    private function client()
    {
        $client = Http::baseUrl($this->baseUrl)
            ->timeout(self::REQUEST_TIMEOUT)
            ->connectTimeout(self::CONNECT_TIMEOUT)
            ->acceptJson();

        if ($this->token !== null && $this->token !== '') {
            $client = $client->withToken($this->token);
        }

        return $client;
    }

    private function mapPayload(array $row): RhUser
    {
        return new RhUser(
            id:                 (string) ($row['id'] ?? $row['uuid'] ?? ''),
            name:               (string) ($row['nombre_completo'] ?? trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''))),
            email:              isset($row['email']) ? (string) $row['email'] : null,
            activo:             (bool) ($row['activo'] ?? false),
            puesto:             $row['puesto']['nombre']        ?? null,
            departamento:       $row['departamento']['nombre']  ?? null,
            area:               $row['area']['nombre']          ?? null,
            employeeId:         isset($row['id']) ? (string) $row['id'] : null,
            razonSocial:        $row['razon_social']['nombre']  ?? null,
            jefeDirectoEmail:   $row['jefe_directo']['email']   ?? null,
        );
    }
}
