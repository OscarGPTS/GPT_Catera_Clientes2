<?php

namespace App\Livewire\Proyectos;

use App\Models\Comercial\Cliente;
use App\Models\Lugar;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportarOportunidades extends Component
{
    use WithFileUploads;

    public $archivo = null;
    public string $modo  = 'agregar';  // agregar | reemplazar
    public string $paso  = 'subir';    // subir | preview | resultado

    /** @var array<int, array<string, mixed>> */
    public array $filas = [];

    public int $insertados = 0;
    public int $omitidos   = 0;

    // ──────────────────────────────────────────────────────────────────────────
    // Paso 1 — Subir y parsear
    // ──────────────────────────────────────────────────────────────────────────

    public function procesarArchivo(): void
    {
        $this->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'archivo.required' => 'Selecciona un archivo.',
            'archivo.mimes'    => 'Solo se aceptan archivos .xlsx, .xls o .csv.',
            'archivo.max'      => 'El archivo no puede superar 10 MB.',
        ]);

        $extension  = strtolower($this->archivo->getClientOriginalExtension());
        $readerType = match ($extension) {
            'xlsx'  => 'Xlsx',
            'xls'   => 'Xls',
            'csv'   => 'Csv',
            default => 'Xlsx',
        };

        $spreadsheet = IOFactory::createReader($readerType)->load($this->archivo->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false); // 0-indexed arrays, formulas resolved

        if (empty($rows)) {
            $this->addError('archivo', 'El archivo está vacío.');
            return;
        }

        // --- Detectar fila de encabezado automáticamente (puede estar en fila 1 o 6+) ---
        $headerRowIdx = 0;
        foreach ($rows as $idx => $row) {
            $cells = array_map(fn($c) => mb_strtolower(trim((string) $c)), $row);
            if (in_array('cp', $cells) || in_array('cliente', $cells)) {
                $headerRowIdx = $idx;
                break;
            }
        }

        $headers = array_map(fn($h) => mb_strtolower(trim((string) $h)), $rows[$headerRowIdx]);
        $col     = $this->buildColumnMap($headers);

        if (! isset($col['cliente_alias'])) {
            $this->addError('archivo', 'No se encontró la columna CLIENTE. Verifica que el archivo tenga encabezados correctos.');
            return;
        }

        // --- Precargar catálogos para lookups -----------------------------------
        $clientes          = Cliente::pluck('id', 'alias')->all();
        $usuarios          = User::pluck('id', 'name')->all();
        $lugares           = Lugar::pluck('id', 'nombre')->all();
        $sinResponsableId  = User::where('email', 'sin.responsable@system.gptservices.com')->value('id');

        $this->filas = [];
        $rawRows     = $sheet->toArray(null, false, true, false);   // raw values (sin resolver) para fechas seriales

        foreach (array_slice($rows, $headerRowIdx + 1) as $i => $row) {
            $rawRow = $rawRows[$headerRowIdx + 1 + $i] ?? [];

            $get = fn(string $key): string =>
                isset($col[$key]) ? trim((string) ($row[$col[$key]] ?? '')) : '';

            $getRaw = fn(string $key): mixed =>
                isset($col[$key]) ? ($rawRow[$col[$key]] ?? null) : null;

            // Saltar filas completamente vacías
            $clienteAlias   = strtoupper($get('cliente_alias'));
            $cpRaw          = $get('cp_numero');
            $techRef        = $get('tech_reference');

            if ($clienteAlias === '' && $cpRaw === '' && $techRef === '') {
                continue;
            }

            // --- Lookups --------------------------------------------------------
            $clienteId      = $clientes[$clienteAlias] ?? null;
            $lugarNombre    = $get('lugar');
            $lugarId        = $lugarNombre !== '' ? ($lugares[$lugarNombre] ?? null) : null;
            $responsableNom = $get('responsable');
            // Use the matched user, or fall back to the "Sin Responsable" system user
            $elaboroId      = $responsableNom !== ''
                ? ($usuarios[$responsableNom] ?? $sinResponsableId)
                : $sinResponsableId;

            // --- Fechas ---------------------------------------------------------
            $fechaEnvio = $this->parseDate($get('fecha_envio'), $getRaw('fecha_envio'));
            $fechaMod   = isset($col['fecha_mod'])
                ? $this->parseDate($get('fecha_mod'), $getRaw('fecha_mod'))
                : null;

            // --- Ponderacion ----------------------------------------------------
            $ponderacion = $this->parsePonderacion($get('ponderacion'), $get('ponderacion_num'));

            // --- Estado ---------------------------------------------------------
            $estado = $this->parseEstado(strtoupper($get('status')));

            // --- Monto ----------------------------------------------------------
            $montoRaw = $get('monto_usd');
            $monto    = $montoRaw !== '' ? (float) str_replace([',', '$', ' '], '', $montoRaw) : null;

            // --- Concepto / Porcentaje adjudicación / Cartera esperada --------
            $conceptoAdj = $get('concepto_adjudicacion') ?: null;
            $porcentajeAdjRaw = $get('porcentaje_adjudicacion');
            $porcentajeAdj = $porcentajeAdjRaw !== ''
                ? (float) str_replace(['%', ',', ' '], '', $porcentajeAdjRaw)
                : null;
            $carteraRaw = $get('cartera_esperada');
            $cartera    = $carteraRaw !== '' ? (float) str_replace([',', '$', ' '], '', $carteraRaw) : null;

            // --- CP (null si -, N/A, vacío) -------------------------------------
            $cpNumero = in_array(strtoupper($cpRaw), ['-', 'N/A', '']) ? null : $cpRaw;

            // --- Validaciones ---------------------------------------------------
            $errores      = [];
            $advertencias = [];

            if (! $clienteId) {
                $advertencias[] = "Cliente '{$clienteAlias}' no existe en el catálogo — se creará como nuevo cliente al confirmar.";
            }
            if ($lugarNombre !== '' && ! $lugarId) {
                $advertencias[] = "Lugar '{$lugarNombre}' no está en el catálogo — lugar_id quedará vacío.";
            }
            if ($responsableNom !== '' && ! ($usuarios[$responsableNom] ?? null)) {
                $advertencias[] = "Responsable '{$responsableNom}' no encontrado — se asignará 'Sin Responsable'.";
            }
            if (! $fechaEnvio) {
                $advertencias[] = "Fecha de envío vacía o inválida.";
            }

            $this->filas[] = [
                'fila'                      => $i + 2,   // número de fila en el archivo (header = fila 1)
                'cp_numero'                 => $cpNumero,
                'cliente_alias'             => $clienteAlias,
                'cliente_id'                => $clienteId,
                'contacto'                  => $get('contacto') ?: null,
                'datos_contacto'            => $get('datos_contacto') ?: null,
                'lugar_nombre'              => $lugarNombre,
                'lugar_id'                  => $lugarId,
                'alcance'                   => $get('alcance') ?: null,
                'tech_reference'            => $techRef ?: null,
                'fecha_envio'               => $fechaEnvio,
                'fecha_modificacion_oferta' => $fechaMod,
                'monto_usd'                 => $monto,
                'hitos_pago'                => $get('hitos_pago') ?: null,
                'elaboro_id'                => $elaboroId,
                'responsable_nombre'        => $responsableNom,
                'estado'                    => $estado,
                'archivo_oferta'            => $get('archivo_oferta') ?: null,
                'ponderacion'               => $ponderacion,
                'concepto_adjudicacion'     => $conceptoAdj,
                'porcentaje_adjudicacion'   => $porcentajeAdj,
                'cartera_esperada'          => $cartera,
                'anio'                      => $fechaEnvio ? (int) substr($fechaEnvio, 0, 4) : (int) date('Y'),
                '_errores'                  => $errores,
                '_advertencias'             => $advertencias,
                '_valido'                   => empty($errores),
            ];
        }

        if (empty($this->filas)) {
            $this->addError('archivo', 'No se encontraron filas de datos en el archivo (solo encabezado o vacío).');
            return;
        }

        $this->paso = 'preview';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Paso 2 — Confirmar importación
    // ──────────────────────────────────────────────────────────────────────────

    public function confirmarImportacion(): void
    {
        $filasValidas = array_filter($this->filas, fn($f) => $f['_valido']);

        DB::transaction(function () use ($filasValidas) {
            if ($this->modo === 'reemplazar') {
                Proyecto::whereIn('estado', ['presentado', 'cotizando', 'cotizado', 'en_revision'])->delete();
            }

            foreach ($filasValidas as $fila) {
                // Resolve cliente: use existing id or auto-create from alias
                $clienteId = $fila['cliente_id'];
                if (! $clienteId && ($fila['cliente_alias'] ?? '') !== '') {
                    $clienteId = Cliente::firstOrCreate(
                        ['alias' => $fila['cliente_alias']],
                        ['razon_social'  => $fila['cliente_alias'], 'activo' => true]
                    )->id;
                }

                $data = [
                    'cliente_id'                => $clienteId,
                    'contacto'                  => $fila['contacto'],
                    'datos_contacto'            => $fila['datos_contacto'],
                    'lugar_id'                  => $fila['lugar_id'],
                    'alcance'                   => $fila['alcance'],
                    'tech_reference'            => $fila['tech_reference'],
                    'fecha_envio'               => $fila['fecha_envio'],
                    'fecha_modificacion_oferta' => $fila['fecha_modificacion_oferta'],
                    'monto_usd'                 => $fila['monto_usd'],
                    'hitos_pago'                => $fila['hitos_pago'],
                    'elaboro_id'                => $fila['elaboro_id'],
                    'estado'                    => $fila['estado'],
                    'archivo_oferta'            => $fila['archivo_oferta'],
                    'ponderacion'               => $fila['ponderacion'],
                    'concepto_adjudicacion'     => $fila['concepto_adjudicacion'],
                    'porcentaje_adjudicacion'   => $fila['porcentaje_adjudicacion'],
                    'cartera_esperada'          => $fila['cartera_esperada'],
                    'anio'                      => $fila['anio'],
                ];

                if ($fila['cp_numero']) {
                    // Upsert: update if cp already exists, create otherwise
                    Proyecto::updateOrCreate(['cp_numero' => $fila['cp_numero']], $data);
                } else {
                    Proyecto::create($data);
                }
                $this->insertados++;
            }

            $this->omitidos = count($this->filas) - $this->insertados;
        });

        $this->paso = 'resultado';
    }

    public function volver(): void
    {
        $this->paso = 'subir';
        $this->filas = [];
        $this->reset('archivo');
        $this->resetErrorBag();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.proyectos.importar-oportunidades');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Construye un mapa [clave_lógica => índice_columna] a partir de los encabezados.
     * Columnas esperadas del CSV GPT Services:
     *   CP | CLIENTE | CONTACTO | DATOS DE CONTACTO | LUGAR | ALCANCE |
     *   OFERTA(tech) | FECHA ENVÍO | FECHA MODIFICACION OFERTA | OFERTAS EMITIDAS |
     *   HITOS DE PAGO | RESPONSABLE | STATUS | OFERTA(PDF) |
     *   CONCEPTO DE ADJUDICACIÓN | % DE ADJUDICACION | % | Cartera Esperada
     */
    private function buildColumnMap(array $headers): array
    {
        $map          = [];
        $ofertaCount  = 0;   // para distinguir OFERTA(tech) del primer OFERTA(PDF)

        foreach ($headers as $idx => $h) {
            if ($h === '') {
                continue;
            }

            // CP ─────────────────────────────────────────────────────────────
            if (preg_match('/^cp\b/', $h)) {
                $map['cp_numero'] ??= $idx;

            // DATOS DE CONTACTO (va antes de CONTACTO para no solapar) ───────
            } elseif (str_contains($h, 'datos') && str_contains($h, 'contacto')) {
                $map['datos_contacto'] ??= $idx;

            // CONTACTO ───────────────────────────────────────────────────────
            } elseif ($h === 'contacto') {
                $map['contacto'] ??= $idx;

            // CLIENTE ────────────────────────────────────────────────────────
            } elseif ($h === 'cliente') {
                $map['cliente_alias'] ??= $idx;

            // LUGAR ──────────────────────────────────────────────────────────
            } elseif ($h === 'lugar') {
                $map['lugar'] ??= $idx;

            // ALCANCE ────────────────────────────────────────────────────────
            } elseif ($h === 'alcance') {
                $map['alcance'] ??= $idx;

            // OFERTAS EMITIDAS (plural, antes de OFERTA singular) ────────────
            } elseif (str_contains($h, 'oferta') && str_contains($h, 'emitida')) {
                $map['monto_usd'] ??= $idx;

            // OFERTA (primera ocurrencia = tech_reference, segunda = PDF) ────────
            } elseif (preg_match('/^oferta\b/', $h)) {
                $ofertaCount++;
                if ($ofertaCount === 1) {
                    $map['tech_reference'] ??= $idx;
                } else {
                    $map['archivo_oferta'] ??= $idx;
                }

            // FECHA MODIFICACION OFERTA ──────────────────────────────────────
            } elseif (str_contains($h, 'modificac')) {
                $map['fecha_mod'] ??= $idx;

            // FECHA ENVÍO ────────────────────────────────────────────────────
            } elseif (str_contains($h, 'fecha') && str_contains($h, 'env')) {
                $map['fecha_envio'] ??= $idx;

            // HITOS DE PAGO ──────────────────────────────────────────────────
            } elseif (str_contains($h, 'hito')) {
                $map['hitos_pago'] ??= $idx;

            // RESPONSABLE ────────────────────────────────────────────────────
            } elseif ($h === 'responsable') {
                $map['responsable'] ??= $idx;

            // STATUS ─────────────────────────────────────────────────────────
            } elseif ($h === 'status' || $h === 'estado') {
                $map['status'] ??= $idx;

            // % DE ADJUDICACION ──────────────────────────────────────────────
            } elseif (str_contains($h, '%') && str_contains($h, 'adjudicaci')) {
                $map['ponderacion'] ??= $idx;

            // CONCEPTO DE ADJUDICACIÓN ───────────────────────────────────────
            } elseif (str_contains($h, 'concepto') && str_contains($h, 'adjudicaci')) {
                $map['concepto_adjudicacion'] ??= $idx;

            // CARTERA ESPERADA ───────────────────────────────────────────────
            } elseif (str_contains($h, 'cartera')) {
                $map['cartera_esperada'] ??= $idx;

            // % (columna numérica porcentaje adjudicación efectivo) ───────────
            } elseif ($h === '%') {
                $map['porcentaje_adjudicacion'] ??= $idx;
                $map['ponderacion_num'] ??= $idx;   // respaldo para parsePonderacion
            }
        }

        return $map;
    }

    /**
     * Convierte la etiqueta de ponderación del CSV al valor numérico del sistema.
     * Acepta texto (REMOTO / POSIBLE / PROBABLE / CONTRATADO) o número directo.
     */
    private function parsePonderacion(string $label, string $num): int
    {
        $label = strtoupper(trim($label));

        return match (true) {
            str_contains($label, 'CONTRATADO') => 100,
            str_contains($label, 'PROBABLE')   => 75,
            str_contains($label, 'POSIBLE')    => 25,
            str_contains($label, 'REMOTO')     => 10,
            is_numeric($label)                 => (int) $label,
            is_numeric($num)                   => (int) $num,
            default                            => 10,
        };
    }

    /** Convierte el STATUS del CSV al valor del enum `estado` de la BD. */
    private function parseEstado(string $status): string
    {
        return match ($status) {
            'ENVIADO', 'PRESENTADO'           => 'presentado',
            'ADJUDICADO'                      => 'adjudicado_pendiente',
            'CANCELADO'                       => 'cancelado',
            'PERDIDO'                         => 'perdido',
            'EN EJECUCION', 'EN EJECUCIÓN'    => 'en_ejecucion',
            default                           => 'presentado',
        };
    }

    /**
     * Parsea una fecha soportando:
     *  - Número serial de Excel (xlsx/xls)
     *  - Cadena en formatos Y-m-d, d/m/Y, m/d/Y, d-m-Y
     */
    private function parseDate(string $strVal, mixed $rawVal): ?string
    {
        if ($strVal === '' || in_array(strtoupper($strVal), ['N/A', '-', 'S/F', 'SIN FECHA'])) {
            return null;
        }

        // Número serial de Excel
        if (is_numeric($rawVal) && (float) $rawVal > 1000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $rawVal)->format('Y-m-d');
            } catch (\Throwable) {
                // continúa con parseo por string
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $strVal);
            if ($d && $d->format($fmt) === $strVal) {
                return $d->format('Y-m-d');
            }
        }

        return null;
    }
}
