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
 * Importa el catálogo maestro desde tech_codification_final.json.
 *
 * Secciones del JSON:
 *   - "Customer"               → clientes (upsert por alias/razon_social)
 *   - "Core Business"          → core_businesses + sizes (la hoja Excel mezcla ambos)
 *   - "GPT Services Personnel" → users (auto-crea si no existe) + personnel_acronyms
 *   - "Country"                → countries
 *   - "TECH REFERENCE"         → tech_references (skip null tech_reference)
 *
 * Idempotente: corre con `php artisan db:seed --class=TechCodificationSeeder`
 * tantas veces como se necesite; usa firstOrCreate / updateOrCreate.
 */
class TechCodificationSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('tech_codification_final.json');
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
            $reporte['clientes']           = $this->importarClientes($data['Customer'] ?? []);
            [$cb, $sizes]                  = $this->importarCoreBusinessYSizes($data['Core Business'] ?? []);
            $reporte['core_businesses']    = $cb;
            $reporte['sizes']              = $sizes;
            $reporte['personnel_acronyms'] = $this->importarPersonnel($data['GPT Services Personnel'] ?? []);
            $reporte['countries']          = $this->importarCountries($data['Country'] ?? []);
            $reporte['tech_references']    = $this->importarTechReferences($data['TECH REFERENCE'] ?? []);
        });

        $this->command->info('── Importación completada ──');
        foreach ($reporte as $tabla => $stats) {
            $line = " · {$tabla}: ";
            $parts = [];
            foreach ($stats as $k => $v) $parts[] = "{$k}={$v}";
            $this->command->line($line . implode(', ', $parts));
        }
    }

    // ── Sección: Customer → clientes ─────────────────────────────────────────
    private function importarClientes(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $skipped = 0;

        foreach ($rows as $row) {
            $nombre  = trim((string) ($row['Customer'] ?? ''));
            $acronym = trim((string) ($row['Acronym']  ?? ''));

            if ($nombre === '' && $acronym === '') { $skipped++; continue; }
            if ($acronym === '') $acronym = $this->generarAlias($nombre);

            // Cliente existente: por alias o por razón social
            $existente = Cliente::where('alias', $acronym)
                ->orWhereRaw('LOWER(razon_social) = ?', [mb_strtolower($nombre)])
                ->first();

            if ($existente) {
                if ($existente->razon_social !== $nombre || $existente->alias !== $acronym) {
                    $existente->update([
                        'razon_social' => $nombre ?: $existente->razon_social,
                        'alias'        => $acronym,
                    ]);
                    $actualizados++;
                } else {
                    $skipped++;
                }
            } else {
                Cliente::create([
                    'razon_social' => $nombre ?: $acronym,
                    'alias'        => $acronym,
                    'activo'       => true,
                ]);
                $insertados++;
            }
        }

        return ['insertados' => $insertados, 'actualizados' => $actualizados, 'omitidos' => $skipped];
    }

    // ── Sección: Core Business (incluye Size + Size.1 como datos paralelos) ──
    private function importarCoreBusinessYSizes(array $rows): array
    {
        $cb = ['insertados' => 0, 'actualizados' => 0, 'omitidos' => 0];
        $sizes = ['insertados' => 0, 'actualizados' => 0, 'omitidos' => 0];

        foreach ($rows as $row) {
            // — Core Business —
            $name = $this->limpiarTexto($row['Core Business'] ?? null);
            $acr  = $this->limpiarTexto($row['Acronym'] ?? null);
            $desc = $this->limpiarTexto($row['Description'] ?? null);

            if ($name !== null && $name !== '-') {
                // Si acronym es '-' lo dejamos null para no chocar con el UNIQUE
                $acrFinal = ($acr === null || $acr === '-') ? null : $acr;

                if ($acrFinal !== null) {
                    $modelo = CoreBusiness::where('acronym', $acrFinal)->first();
                } else {
                    $modelo = CoreBusiness::where('core_business', $name)->first();
                }

                if ($modelo) {
                    $modelo->update(['core_business' => $name, 'description' => $desc, 'acronym' => $acrFinal]);
                    $cb['actualizados']++;
                } else {
                    CoreBusiness::create([
                        'core_business' => $name,
                        'acronym'       => $acrFinal,
                        'description'   => $desc,
                    ]);
                    $cb['insertados']++;
                }
            } else {
                $cb['omitidos']++;
            }

            // — Size (cada fila tiene Size principal y Size.1 como datos paralelos) —
            $principal = $this->parseSizePrincipal($row['Size'] ?? null);
            $secund    = $this->limpiarTexto($row['Size.1'] ?? null);
            if ($secund === '-' || $secund === '') $secund = null;

            if ($principal !== null) {
                $existe = Size::where('size_principal', $principal)
                    ->where(function ($q) use ($secund) {
                        if ($secund === null) $q->whereNull('size_secundario');
                        else $q->where('size_secundario', $secund);
                    })
                    ->first();
                if (!$existe) {
                    Size::create(['size_principal' => $principal, 'size_secundario' => $secund]);
                    $sizes['insertados']++;
                } else {
                    $sizes['omitidos']++;
                }
            } else {
                $sizes['omitidos']++;
            }
        }

        return [$cb, $sizes];
    }

    // ── Sección: GPT Services Personnel ──────────────────────────────────────
    // Match por users.name; si no existe, auto-crear usuario.
    private function importarPersonnel(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $usersCreados = 0; $skipped = 0;

        foreach ($rows as $row) {
            $nombre  = trim((string) ($row['Personnel'] ?? ''));
            $acronym = trim((string) ($row['Acronym'] ?? ''));
            $accMgr  = trim((string) ($row['Account Manager'] ?? ''));

            if ($acronym === '') { $skipped++; continue; }

            // Match user por name (case-insensitive); si no existe lo creamos
            $user = User::whereRaw('LOWER(name) = ?', [mb_strtolower($nombre)])->first();

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

    // ── Sección: Country ─────────────────────────────────────────────────────
    private function importarCountries(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $skipped = 0;

        foreach ($rows as $row) {
            $state   = $this->limpiarTexto($row['State'] ?? null);
            $zone    = $this->limpiarTexto($row['Zone'] ?? null);
            $country = $this->limpiarTexto($row['Country'] ?? null) ?: 'MEX';

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

    // ── Sección: TECH REFERENCE ──────────────────────────────────────────────
    private function importarTechReferences(array $rows): array
    {
        $insertados = 0; $actualizados = 0; $skipNoRef = 0; $skipDup = 0;

        // Cache de clientes (alias y razon_social en lowercase → id)
        $clientesPorAlias = Cliente::pluck('id', 'alias')->all();
        $clientesPorNombre = [];
        foreach (Cliente::get(['id', 'razon_social']) as $c) {
            $clientesPorNombre[mb_strtolower($c->razon_social)] = $c->id;
        }

        foreach ($rows as $row) {
            $techRef = trim((string) ($row['Tech Reference'] ?? ''));
            if ($techRef === '') { $skipNoRef++; continue; }

            // Resolver cliente_id por nombre / acronym
            $customer = trim((string) ($row['Customer'] ?? ''));
            $clienteId = $clientesPorAlias[$customer]
                ?? $clientesPorNombre[mb_strtolower($customer)]
                ?? null;

            $payload = [
                'cliente_id'              => $clienteId,
                'fecha_referencia'        => $this->parseFechaYYMMDD($row['Date (YYMMDD)'] ?? null),
                'cp_numero'               => $this->limpiarTexto($row['CP'] ?? null),
                'revision'                => (int) ($row['Quote / CP Revision'] ?? 0),
                'contacto'                => $this->limpiarTexto($row['Contact'] ?? null),
                'estado_cliente'          => $this->limpiarTexto($row['State'] ?? null),
                'pais'                    => $this->limpiarTexto($row['Country'] ?? null),
                'zona'                    => $this->limpiarTexto($row['Zone'] ?? null),
                'nombre_proyecto_cliente' => $this->limpiarTexto($row['Customer Project Name'] ?? null),
                'core_business'           => $this->limpiarTexto($row['Core Business'] ?? null),
                'pipe_in'                 => $this->parseNumerico($row['Pipe (in)'] ?? null),
                'branch_in'               => $this->parseNumerico($row['Branch (in)'] ?? null),
                'descripcion_larga'       => $this->limpiarTexto($row['Long Description'] ?? null),
                'amount_usd'              => $this->parseNumerico($row['Amount  (USD)'] ?? null),
                'amount_mxn'              => $this->parseNumerico($row['Amount  (MXN)'] ?? null),
                'quotation_personnel'     => $this->limpiarTexto($row['Quotation Personnel'] ?? null),
                'account_manager'         => $this->limpiarTexto($row['Account  Manager'] ?? null),
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
        $s = preg_replace('/[^\d.\-]/', '', (string) $v);
        if ($s === '' || $s === '-' || $s === '.') return null;
        return is_numeric($s) ? (float) $s : null;
    }

    /** Size principal: solo numéricos válidos para columna decimal(8,2). */
    private function parseSizePrincipal(mixed $v): ?float
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '' || $s === '-') return null;
        // Algunos valores son strings tipo "30"
        return is_numeric($s) ? (float) $s : null;
    }

    /** Date (YYMMDD) en float/int → 'YYMMDD' string (6 chars). */
    private function parseFechaYYMMDD(mixed $v): ?string
    {
        if ($v === null || $v === '') return null;
        $s = preg_replace('/\D/', '', (string) $v);   // quita decimales y separadores
        if ($s === '') return null;
        // El Excel a veces guarda 190603.0 → "1906030"; tomar primeros 6
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

        // Garantiza unicidad
        $candidato = $email;
        $n = 1;
        while (User::where('email', $candidato)->exists()) {
            $n++;
            $candidato = "{$base}{$n}@gptservices.local";
        }
        return $candidato;
    }
}
