<?php

namespace Database\Seeders;

use App\Models\Comercial\Cliente;
use App\Models\CoreBusiness;
use App\Models\Country;
use App\Models\PersonnelAcronym;
use App\Models\Size;
use App\Models\TechReference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Importa el catálogo maestro desde Tech_Codification_2_0_clean.json.
 *
 * Secciones del JSON:
 *   - "customers"        → clientes (upsert por alias o razon_social)
 *   - "core_businesses"  → core_businesses
 *   - "personnel"        → users (auto-crea si no existe) + personnel_acronyms
 *   - "countries"        → countries
 *   - "tech_references"  → tech_references (skip null tech_reference)
 *
 * Sizes se derivan automáticamente de los valores únicos de pipe_in/branch_in
 * en tech_references.
 *
 * Idempotente: corre con `php artisan db:seed --class=TechCodificationSeeder`
 * tantas veces como se necesite; usa upsert por clave única.
 */
class TechCodificationSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('Tech_Codification_2_0_clean.json');
        if (!is_file($jsonPath)) {
            $this->command->error("No se encontró {$jsonPath}");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($data)) {
            $this->command->error('JSON inválido.');
            return;
        }

        $reporte = [];

        DB::transaction(function () use ($data, &$reporte) {
            // Orden importante: clientes primero (referenciados por tech_references)
            $reporte['clientes']           = $this->importarClientes($data['customers'] ?? []);
            $reporte['core_businesses']    = $this->importarCoreBusinesses($data['core_businesses'] ?? []);
            $reporte['personnel_acronyms'] = $this->importarPersonnel($data['personnel'] ?? []);
            $reporte['countries']          = $this->importarCountries($data['countries'] ?? []);
            $reporte['tech_references']    = $this->importarTechReferences($data['tech_references'] ?? []);
            $reporte['sizes']              = $this->importarSizes($data['tech_references'] ?? []);
        });

        $this->command->info('── Importación completada ──');
        foreach ($reporte as $tabla => $stats) {
            $line = " · {$tabla}: ";
            $parts = [];
            foreach ($stats as $k => $v) $parts[] = "{$k}={$v}";
            $this->command->line($line . implode(', ', $parts));
        }
    }

    // ── Sección: customers → clientes ────────────────────────────────────────
    //
    // Estrategia:
    //   1) Match por alias (acronym del JSON). Si existe, no toca alias ni nombre
    //      (preserva los valores curados de CatalogosBaseSeeder).
    //   2) Si no hay match por alias, match por razon_social (case-insensitive).
    //      Si existe, NO sobreescribe el alias curado; solo lo deja igual.
    //   3) Si no hay match, inserta nuevo cliente con el alias del JSON.
    //
    // Esto evita conflictos cuando un mismo nombre tiene aliases distintos
    // entre catálogos (ej.: PIFUSA = PIF curado vs PFA en JSON).
    private function importarClientes(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $skipped = 0;

        foreach ($rows as $row) {
            $nombre  = trim((string) ($row['customer_name'] ?? ''));
            $acronym = trim((string) ($row['acronym']       ?? ''));

            if ($nombre === '' && $acronym === '') { $skipped++; continue; }
            if ($acronym === '') $acronym = $this->generarAlias($nombre);

            // 1) Match por alias
            $existente = $acronym !== '' ? Cliente::where('alias', $acronym)->first() : null;

            // 2) Match por razon_social
            if (!$existente && $nombre !== '') {
                $existente = Cliente::whereRaw('LOWER(razon_social) = ?', [mb_strtolower($nombre)])->first();
            }

            if ($existente) {
                $skipped++;   // preserva los datos curados (alias, sector, segmento)
            } else {
                Cliente::create([
                    'razon_social' => $nombre ?: $acronym,
                    'alias'        => $acronym,
                    'activo'       => true,
                ]);
                $insertados++;
            }
        }

        return ['insertados' => $insertados, 'preservados' => $skipped, 'actualizados' => $actualizados];
    }

    // ── Sección: core_businesses ─────────────────────────────────────────────
    private function importarCoreBusinesses(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $omitidos = 0;

        foreach ($rows as $row) {
            $name = $this->limpiarTexto($row['core_business'] ?? null);
            $acr  = $this->limpiarTexto($row['acronym']        ?? null);
            $desc = $this->limpiarTexto($row['description']    ?? null);

            if ($name === null) { $omitidos++; continue; }

            $modelo = $acr !== null
                ? CoreBusiness::where('acronym', $acr)->first()
                : CoreBusiness::where('core_business', $name)->first();

            if ($modelo) {
                $modelo->update([
                    'core_business' => $name,
                    'acronym'       => $acr,
                    'description'   => $desc,
                ]);
                $actualizados++;
            } else {
                CoreBusiness::create([
                    'core_business' => $name,
                    'acronym'       => $acr,
                    'description'   => $desc,
                ]);
                $insertados++;
            }
        }

        return ['insertados' => $insertados, 'actualizados' => $actualizados, 'omitidos' => $omitidos];
    }

    // ── Sección: personnel ───────────────────────────────────────────────────
    // Match por users.name; si no existe, auto-crear usuario.
    private function importarPersonnel(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $usersCreados = 0; $skipped = 0;

        foreach ($rows as $row) {
            $nombre  = trim((string) ($row['personnel_name']  ?? ''));
            $acronym = trim((string) ($row['acronym']         ?? ''));
            $accMgr  = trim((string) ($row['account_manager'] ?? ''));

            if ($acronym === '') { $skipped++; continue; }

            $user = $nombre !== ''
                ? User::whereRaw('LOWER(name) = ?', [mb_strtolower($nombre)])->first()
                : null;

            if (!$user && $nombre !== '') {
                $user = User::create([
                    'name'     => $nombre,
                    'email'    => $this->emailSugerido($acronym, $nombre),
                    'password' => Hash::make(Str::random(40)),
                    'status'   => 'active',
                ]);
                $usersCreados++;
            }

            $existente = PersonnelAcronym::where('acronym', $acronym)->first();
            if ($existente) {
                $existente->update([
                    'user_id'         => $user?->id,
                    'account_manager' => $accMgr ?: null,
                ]);
                $actualizados++;
            } else {
                PersonnelAcronym::create([
                    'user_id'         => $user?->id,
                    'acronym'         => $acronym,
                    'account_manager' => $accMgr ?: null,
                ]);
                $insertados++;
            }
        }

        return ['insertados' => $insertados, 'actualizados' => $actualizados, 'users_creados' => $usersCreados, 'omitidos' => $skipped];
    }

    // ── Sección: countries ───────────────────────────────────────────────────
    private function importarCountries(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $skipped = 0;

        foreach ($rows as $row) {
            $state   = $this->limpiarTexto($row['state']   ?? null);
            $zone    = $this->limpiarTexto($row['zone']    ?? null);
            $country = $this->limpiarTexto($row['country'] ?? null) ?: 'MEX';

            if (!$state) { $skipped++; continue; }

            $existente = Country::where('state', $state)->where('country', $country)->first();
            if ($existente) {
                $existente->update(['zone' => $zone ?: $existente->zone]);
                $actualizados++;
            } else {
                Country::create(['state' => $state, 'zone' => $zone, 'country' => $country]);
                $insertados++;
            }
        }

        return ['insertados' => $insertados, 'actualizados' => $actualizados, 'omitidos' => $skipped];
    }

    // ── Sección: tech_references ─────────────────────────────────────────────
    private function importarTechReferences(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $skipNoRef = 0; $skipDup = 0;

        // Cache de clientes (alias y razon_social en lowercase → id)
        $clientesPorAlias  = Cliente::pluck('id', 'alias')->all();
        $clientesPorNombre = [];
        foreach (Cliente::get(['id', 'razon_social']) as $c) {
            $clientesPorNombre[mb_strtolower($c->razon_social)] = $c->id;
        }

        foreach ($rows as $row) {
            $techRef = trim((string) ($row['tech_reference'] ?? ''));
            if ($techRef === '') { $skipNoRef++; continue; }

            $customer  = trim((string) ($row['customer'] ?? ''));
            $clienteId = $clientesPorAlias[$customer]
                ?? $clientesPorNombre[mb_strtolower($customer)]
                ?? null;

            $payload = [
                'cliente_id'              => $clienteId,
                'fecha_referencia'        => $this->parseFechaYYMMDD($row['date'] ?? null),
                'cp_numero'               => $this->limpiarTexto($row['cp'] ?? null),
                'revision'                => (int) ($row['quote_cp_revision'] ?? 0),
                'contacto'                => $this->limpiarTexto($row['contact'] ?? null),
                'estado_cliente'          => $this->limpiarTexto($row['state'] ?? null),
                'pais'                    => $this->limpiarTexto($row['country'] ?? null),
                'zona'                    => $this->limpiarTexto($row['zone'] ?? null),
                'nombre_proyecto_cliente' => $this->limpiarTexto($row['customer_project_name'] ?? null),
                'core_business'           => $this->limpiarTexto($row['core_business'] ?? null),
                'pipe_in'                 => $this->parseNumerico($row['pipe_in'] ?? null),
                'branch_in'               => $this->parseNumerico($row['branch_in'] ?? null),
                'descripcion_larga'       => $this->limpiarTexto($row['long_description'] ?? null),
                'amount_usd'              => $this->parseNumerico($row['amount_usd'] ?? null),
                'amount_mxn'              => $this->parseNumerico($row['amount_mxn'] ?? null),
                'quotation_personnel'     => $this->limpiarTexto($row['quotation_personnel'] ?? null),
                'account_manager'         => $this->limpiarTexto($row['account_manager'] ?? null),
            ];

            $existente = TechReference::where('tech_reference', $techRef)->first();
            if ($existente) {
                $existente->update($payload);
                $actualizados++;
            } else {
                try {
                    TechReference::create(['tech_reference' => $techRef] + $payload);
                    $insertados++;
                } catch (\Illuminate\Database\QueryException $e) {
                    $skipDup++;
                }
            }
        }

        return [
            'insertados'      => $insertados,
            'actualizados'    => $actualizados,
            'omitidos_nulos'  => $skipNoRef,
            'omitidos_error'  => $skipDup,
        ];
    }

    // ── Sizes: deriva los tamaños únicos vistos en tech_references ───────────
    private function importarSizes(array $rows): array
    {
        $insertados = 0; $omitidos = 0;
        $vistos = [];

        foreach ($rows as $row) {
            foreach (['pipe_in', 'branch_in'] as $campo) {
                $valor = $this->parseNumerico($row[$campo] ?? null);
                if ($valor === null) continue;
                $key = (string) $valor;
                if (isset($vistos[$key])) continue;
                $vistos[$key] = true;

                $existe = Size::where('size_principal', $valor)->whereNull('size_secundario')->first();
                if ($existe) { $omitidos++; continue; }

                Size::create(['size_principal' => $valor, 'size_secundario' => null]);
                $insertados++;
            }
        }

        return ['insertados' => $insertados, 'omitidos' => $omitidos];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function limpiarTexto(mixed $v): ?string
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '' || $s === '-' || strcasecmp($s, 'null') === 0) return null;
        return $s;
    }

    /** Parsea número desde strings con comas / símbolos. */
    private function parseNumerico(mixed $v): ?float
    {
        if ($v === null || $v === '' || $v === '-') return null;
        if (is_numeric($v)) return (float) $v;
        $s = preg_replace('/[^\d.\-]/', '', (string) $v);
        if ($s === '' || $s === '-' || $s === '.') return null;
        return is_numeric($s) ? (float) $s : null;
    }

    /** Date (YYMMDD) en float/int → 'YYMMDD' string (6 chars). */
    private function parseFechaYYMMDD(mixed $v): ?string
    {
        if ($v === null || $v === '') return null;
        $s = preg_replace('/\D/', '', (string) $v);
        if ($s === '') return null;
        return substr($s, 0, 6);
    }

    /** Genera un alias razonable cuando el JSON no trae acronym. */
    private function generarAlias(string $nombre): string
    {
        $clean = preg_replace('/[^A-Za-z0-9 ]/', '', $nombre);
        $parts = preg_split('/\s+/', trim((string) $clean));
        $alias = '';
        foreach ($parts as $p) {
            if ($p === '') continue;
            $alias .= strtoupper(substr($p, 0, 1));
        }
        $alias = substr($alias, 0, 8);
        return $alias !== '' ? $alias : ('CLI' . substr(md5($nombre), 0, 5));
    }

    /** Email "auto" para usuarios creados desde el catálogo de personnel. */
    private function emailSugerido(string $acronym, string $nombre): string
    {
        $base = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $acronym ?: $nombre));
        if ($base === '') $base = 'personnel' . substr(md5($nombre), 0, 6);
        $email = "{$base}@gptservices.local";

        $candidato = $email;
        $n = 1;
        while (User::where('email', $candidato)->exists()) {
            $n++;
            $candidato = "{$base}{$n}@gptservices.local";
        }
        return $candidato;
    }
}
