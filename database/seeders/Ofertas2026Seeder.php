<?php

namespace Database\Seeders;

use App\Models\Comercial\Cliente;
use App\Models\Lugar;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Carga las ofertas 2026 desde Status_ofertas_GPT_Services_2026.json.
 *
 * Mapeo columna del JSON → campo de proyectos:
 *   CP                     → cp_numero (normaliza '-' y null)
 *   CLIENTE                → cliente_id (vía mapa nombre → alias)
 *   CONTACTO               → contacto
 *   DATOS DE CONTACTO      → datos_contacto (limpia "Teléfono:\nCorreo:" vacíos)
 *   LUGAR                  → lugar_id (vía nombre)
 *   ALCANCE                → alcance
 *   OFERTA   (técnica)     → tech_reference
 *   FECHA ENVÍO            → fecha_envio (normaliza fechas en español)
 *   FECHA MODIFICACION...  → fecha_modificacion_oferta
 *   MONTO USD              → monto_usd (admite strings "$ 23,527.43")
 *   HITOS DE PAGO          → hitos_pago
 *   RESPONSABLE            → elaboro_id (vía users.name)
 *   STATUS                 → estado ('ENVIADO' → 'presentado')
 *   OFERTA (PDF)           → archivo_oferta
 *   CONCEPTO DE ADJUDICACIÓN → concepto_adjudicacion
 *   % DE ADJUDICACION      → ponderacion
 *
 * Notas: cp_numero duplicado en el JSON (016/26 aparece dos veces) se diferencia
 * con el sufijo '-B'. cartera_esperada se calcula en vivo en el modelo
 * (monto_usd × ponderacion / 100).
 */
class Ofertas2026Seeder extends Seeder
{
    /**
     * Mapeo entre el nombre del cliente como aparece en el JSON de ofertas
     * y el alias del cliente en la tabla `clientes`.
     */
    private const CLIENTE_ALIAS_MAP = [
        'IGASAMEX'        => 'IGA',
        'ENGIE'           => 'ENG',
        'PIR SYSTEM'      => 'PIR',
        'NATURGY'         => 'NAT',
        'PROTEXA'         => 'PTX',
        'EUROINOVA'       => 'EIN',
        'MOLPER'          => 'MOL',
        'SERPORT'         => 'SRP',
        'GCI'             => 'GCI',
        'ICA'             => 'ICA',
        'SICIM'           => 'SIC',
        'COPC'            => 'COP',
        'INDHECA'         => 'IGC',
        'SARREAL'         => 'SAR',
        'ARSEAL'          => 'ARS',
        'ESENTIA'         => 'ESE',
        'SEDENA'          => 'SDN',
        'TC ENERGY'       => 'TCE',
        'GRUPO 3VTA'      => 'G3V',
        'GEOLIS'          => 'GSO',
        'PIFUSA'          => 'PIF',
        'COCOMEX'         => 'COC',
        'MARABIS ENERGY'  => 'ME',
    ];

    public function run(): void
    {
        $jsonPath = base_path('Status_ofertas_GPT_Services_2026.json');
        if (!is_file($jsonPath)) {
            $this->command->error("No se encontró {$jsonPath}");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($data) || !isset($data['ofertas']) || !is_array($data['ofertas'])) {
            $this->command->error('JSON inválido o sin sección "ofertas".');
            return;
        }

        $ofertas = $data['ofertas'];

        // Conteo de CP repetidos para sufijar duplicados (016/26 → 016/26, 016-B/26, ...)
        $vistosCp = [];

        $insertados = 0; $omitidos = 0;
        foreach ($ofertas as $row) {
            $clienteNombre = (string) ($row['CLIENTE'] ?? '');
            $alias         = self::CLIENTE_ALIAS_MAP[$clienteNombre] ?? null;
            if (!$alias) {
                $this->command->warn("Cliente sin mapeo: '{$clienteNombre}' — registro omitido.");
                $omitidos++; continue;
            }

            $clienteId = Cliente::where('alias', $alias)->value('id');
            if (!$clienteId) {
                $this->command->warn("Cliente alias '{$alias}' no encontrado — registro omitido.");
                $omitidos++; continue;
            }

            $cpNumero = $this->normalizarCp($row['CP'] ?? null);
            if ($cpNumero !== null) {
                $vistosCp[$cpNumero] = ($vistosCp[$cpNumero] ?? 0) + 1;
                if ($vistosCp[$cpNumero] > 1) {
                    $sufijo = chr(ord('A') + $vistosCp[$cpNumero] - 2);
                    [$num, $anio] = array_pad(explode('/', $cpNumero, 2), 2, '');
                    $cpNumero = $anio !== '' ? "{$num}-{$sufijo}/{$anio}" : "{$cpNumero}-{$sufijo}";
                }
            }

            $fechaEnvio    = $this->normalizarFecha($row['FECHA ENVÍO'] ?? null);
            $fechaModOferta = $this->normalizarFecha($row['FECHA MODIFICACION OFERTA'] ?? null);

            Proyecto::create([
                'cp_numero'                 => $cpNumero,
                'cliente_id'                => $clienteId,
                'contacto'                  => $this->limpiarTexto($row['CONTACTO'] ?? null),
                'datos_contacto'            => $this->limpiarDatosContacto($row['DATOS DE CONTACTO'] ?? null),
                'lugar_id'                  => $this->resolverLugar($row['LUGAR'] ?? null),
                'alcance'                   => $this->limpiarTexto($row['ALCANCE'] ?? null),
                'tech_reference'            => $this->limpiarTexto($row['OFERTA   '] ?? ($row['OFERTA'] ?? null)),
                'fecha_envio'               => $fechaEnvio,
                'fecha_modificacion_oferta' => $fechaModOferta,
                'monto_usd'                 => $this->parseNumerico($row['MONTO USD'] ?? $row['OFERTAS EMITIDAS'] ?? null),
                'hitos_pago'                => $this->limpiarTexto($row['HITOS DE PAGO'] ?? null),
                'elaboro_id'                => $this->resolverUser($row['RESPONSABLE'] ?? null),
                'estado'                    => $this->mapearEstado($row['STATUS'] ?? null),
                'archivo_oferta'            => $this->limpiarTexto($row['OFERTA'] ?? null),
                'concepto_adjudicacion'     => $this->limpiarTexto($row['CONCEPTO DE ADJUDICACIÓN'] ?? null),
                'ponderacion'               => (int) ($row['% DE ADJUDICACION'] ?? 10),
                'anio'                      => $fechaEnvio ? (int) substr($fechaEnvio, 0, 4) : (int) date('Y'),
            ]);

            $insertados++;
        }

        $this->command->info("Ofertas2026Seeder: {$insertados} insertados, {$omitidos} omitidos.");
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function limpiarTexto(mixed $v): ?string
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '' || $s === '-' || strcasecmp($s, 'null') === 0) return null;
        return $s;
    }

    /** "Teléfono:\nCorreo: \nmail@x.com" → solo deja líneas con contenido real. */
    private function limpiarDatosContacto(mixed $v): ?string
    {
        if ($v === null) return null;
        $lineas = preg_split('/\r?\n/', (string) $v) ?: [];
        $out = [];
        foreach ($lineas as $linea) {
            $l = trim($linea);
            if ($l === '') continue;
            // Líneas tipo "Teléfono:" o "Correo:" sin valor → omitir
            if (preg_match('/^(Tel[eé]fono|Correo)\s*:\s*$/iu', $l)) continue;
            $out[] = $l;
        }
        $s = implode("\n", $out);
        return $s !== '' ? $s : null;
    }

    /** Normaliza CP: '-', '', null y 'CP-021/26' → '021/26'. */
    private function normalizarCp(mixed $v): ?string
    {
        $s = $this->limpiarTexto($v);
        if ($s === null) return null;
        // Quitar prefijo "CP-" si lo trae
        return preg_replace('/^CP-/i', '', $s);
    }

    /**
     * Convierte:
     *   '2026-02-23'                        → '2026-02-23'
     *   'Miercoles, febrero 11, 2026'       → '2026-02-11'
     *   'miércoles,febrero 11, 2026'        → '2026-02-11'
     *   '-' / null / ''                     → null
     */
    private function normalizarFecha(mixed $v): ?string
    {
        $s = $this->limpiarTexto($v);
        if ($s === null) return null;

        // ISO ya válido
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;

        $meses = [
            'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04',
            'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08',
            'septiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12',
        ];
        $normal = mb_strtolower(strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']));
        if (preg_match('/([a-z]+)\s+(\d{1,2}),?\s*(\d{4})/u', $normal, $m)) {
            $mes = $meses[$m[1]] ?? null;
            if ($mes) {
                return sprintf('%04d-%s-%02d', (int) $m[3], $mes, (int) $m[2]);
            }
        }
        // Último intento: dejar que strtotime maneje formatos
        $ts = strtotime($s);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /** Parsea monto: número, "$ 1,234.56", "1234.56", etc. */
    private function parseNumerico(mixed $v): ?float
    {
        if ($v === null || $v === '' || $v === '-') return null;
        if (is_numeric($v)) return (float) $v;
        $s = preg_replace('/[^\d.\-]/', '', (string) $v);
        if ($s === '' || $s === '-' || $s === '.') return null;
        return is_numeric($s) ? (float) $s : null;
    }

    private function resolverLugar(?string $nombre): ?int
    {
        $n = $this->limpiarTexto($nombre);
        if ($n === null) return null;
        return Lugar::where('nombre', $n)->value('id');
    }

    private function resolverUser(?string $nombre): ?int
    {
        $n = $this->limpiarTexto($nombre);
        if ($n === null) return null;
        return User::where('name', $n)->value('id');
    }

    private function mapearEstado(?string $status): string
    {
        $s = mb_strtoupper(trim((string) $status));
        return match ($s) {
            'ENVIADO'    => 'presentado',
            'PRESENTADO' => 'presentado',
            'COTIZADO'   => 'cotizado',
            'COTIZANDO'  => 'cotizando',
            default      => 'presentado',
        };
    }
}
