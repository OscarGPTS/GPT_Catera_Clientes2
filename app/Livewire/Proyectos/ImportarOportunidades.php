<?php

namespace App\Livewire\Proyectos;

use App\Models\Comercial\Cliente;
use App\Models\Lugar;
use App\Models\Ponderacion;
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
        $rawRows     = $sheet->toArray(null, false, false, false);  // raw values SIN formatear: fechas llegan como serial numérico

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
            $ponderacion   = $this->parsePonderacion($get('ponderacion'), $get('ponderacion_num'));
            $ponderacionId = $this->resolverPonderacionId($get('ponderacion'), $ponderacion);

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
                'ponderacion_id'            => $ponderacionId,
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
                // Desactivar FK checks para poder truncar en cualquier orden
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                // ── Tablas hijo de proyectos ───────────────────────────────────
                DB::table('cotizacion_partidas')->truncate();
                DB::table('cotizaciones')->truncate();
                DB::table('bitacora_diaria')->truncate();
                DB::table('bom_boe_items')->truncate();
                DB::table('cartas_finiquito')->truncate();
                DB::table('cierres_mensuales')->truncate();
                DB::table('cierres_secciones')->truncate();
                DB::table('cierres_lineas')->truncate();
                DB::table('cronograma_actividades')->truncate();
                DB::table('cronogramas')->truncate();
                DB::table('kick_off_meetings')->truncate();
                DB::table('libro_seccion_checklist')->truncate();
                DB::table('libro_secciones')->truncate();
                DB::table('libro_documentos')->truncate();
                DB::table('libros_proyecto')->truncate();
                DB::table('listados_suministros_items')->truncate();
                DB::table('listados_suministros')->truncate();
                DB::table('minuta_entrega_participantes')->truncate();
                DB::table('minutas_entrega')->truncate();
                DB::table('post_mortem')->truncate();
                DB::table('asignaciones_personas')->truncate();
                DB::table('proyecto_asignaciones')->truncate();
                DB::table('proyecto_miembros')->truncate();
                DB::table('proyecto_eventos')->truncate();
                DB::table('reportes_semanales')->truncate();
                DB::table('solicitudes_internas_items')->truncate();
                DB::table('solicitudes_internas')->truncate();
                DB::table('viaticos_partidas')->truncate();
                DB::table('viaticos_personal')->truncate();
                DB::table('solicitudes_viaticos')->truncate();

                // Movimientos bancarios: solo nullificar la referencia, no borrar registros financieros
                DB::table('movimientos_bancarios')->update(['conciliado_con_proyecto_id' => null]);

                DB::table('proyectos')->truncate();

                // ── Tablas hijo de clientes ────────────────────────────────────
                DB::table('contactos_cliente')->truncate();
                DB::table('tech_references')->truncate();
                DB::table('clientes')->truncate();

                DB::statement('SET FOREIGN_KEY_CHECKS=1');
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
                    $proyecto = Proyecto::updateOrCreate(['cp_numero' => $fila['cp_numero']], $data);
                } else {
                    $proyecto = Proyecto::create($data);
                }

                // Registrar al elaborador como creador en proyecto_miembros
                if ($fila['elaboro_id']) {
                    $proyecto->miembros()->updateOrCreate(
                        ['rol' => 'creador'],
                        ['user_id' => $fila['elaboro_id']]
                    );
                }

                // Snapshot de ponderación para el mes vigente
                if ($fila['ponderacion_id'] ?? null) {
                    $proyecto->registrarPonderacion(
                        (int) $fila['ponderacion_id'],
                        notas: 'Importado desde Excel (' . $fila['fila'] . ').'
                    );
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
            str_contains($label, 'PERDIDA')    => 0,
            str_contains($label, 'PRESUPUESTAL') => 0,
            is_numeric($label)                 => (int) $label,
            is_numeric($num)                   => (int) $num,
            default                            => 10,
        };
    }

    /**
     * Resuelve el id de la fila en el catálogo `ponderaciones`.
     * Prioriza el matching por texto (CONTRATADO, PROBABLE…); si no, cae al porcentaje.
     */
    private function resolverPonderacionId(string $label, int $porcentaje): ?int
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Ponderacion::active()->orderBy('orden')->get();
        }
        if ($cache->isEmpty()) return null;

        $label = strtoupper(trim($label));
        if ($label !== '') {
            foreach ($cache as $p) {
                if (str_contains($label, strtoupper($p->concepto))) {
                    return $p->id;
                }
            }
        }

        // Match por porcentaje exacto
        $exact = $cache->firstWhere('porcentaje', $porcentaje);
        if ($exact) return $exact->id;

        // Fallback: el más cercano
        return $cache->sortBy(fn($p) => abs($p->porcentaje - $porcentaje))->first()?->id;
    }

    /** Convierte el STATUS del CSV al valor del enum `estado` de la BD. */
    private function parseEstado(string $status): string
    {
        return match ($status) {
            'ENVIADO'                         => 'enviado',
            'PRESENTADO'                      => 'presentado',
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

        // Fecha larga en español: "martes, 18 de noviembre de 2025" o "18 de noviembre de 2025"
        if (preg_match('/(?:\w+,?\s+)?(\d{1,2})\s+de\s+([a-záéíóúü]+)\s+de\s+(\d{4})/ui', $strVal, $m)) {
            $meses = [
                'enero'=>1,'febrero'=>2,'marzo'=>3,'abril'=>4,'mayo'=>5,'junio'=>6,
                'julio'=>7,'agosto'=>8,'septiembre'=>9,'octubre'=>10,'noviembre'=>11,'diciembre'=>12,
            ];
            $mes = $meses[mb_strtolower($m[2])] ?? null;
            if ($mes) {
                return sprintf('%04d-%02d-%02d', (int) $m[3], $mes, (int) $m[1]);
            }
        }

        return null;
    }
}
