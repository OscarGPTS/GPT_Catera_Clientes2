<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Cliente;
use App\Models\CoreBusiness;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Lugar;
use App\Models\PersonnelAcronym;
use App\Models\Proyectos\Proyecto;
use App\Models\Size;
use App\Models\TechReference;
use App\Models\Vario;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CatalogosController extends Controller
{
    /**
     * Definición de los catálogos disponibles.
     *
     * fields:           columnas del Excel en orden (A, B…); null = columna que se omite.
     *                   Nombres virtuales (en lookups) se resuelven a FK antes de guardar.
     * unique:           subconjunto de fields (o targets) para updateOrCreate.
     * headers:          etiqueta de cada columna de importación (para el modal).
     * lookups:          resolución de FKs: campo virtual → {target, model, column}.
     * display_fields:   columnas a mostrar en la tabla de la vista (fallback → fields sin nulls).
     * display_headers:  cabeceras de esa tabla (fallback → headers sin nulls).
     */
    private const CATALOGOS = [
        'customers' => [
            'model'   => Customer::class,
            'label'   => 'Customers',
            'fields'  => ['customer', 'acronym'],
            'unique'  => ['acronym'],
            'headers' => ['Customer', 'Acronym'],
        ],
        'core_businesses' => [
            'model'   => CoreBusiness::class,
            'label'   => 'Core Business',
            'fields'  => ['core_business', 'acronym', 'description'],
            'unique'  => ['acronym'],
            'headers' => ['Core Business', 'Acronym', 'Description'],
        ],
        'varios' => [
            'model'   => Vario::class,
            'label'   => 'Varios',
            'fields'  => ['nombre'],
            'unique'  => ['nombre'],
            'headers' => ['Nombre'],
        ],
        'sizes' => [
            'model'   => Size::class,
            'label'   => 'Sizes',
            'fields'  => ['size_principal', 'size_secundario'],
            'unique'  => ['size_principal', 'size_secundario'],
            'headers' => ['Size Principal (in)', 'Size Secundario'],
        ],
        'personnel_acronyms' => [
            'model'   => PersonnelAcronym::class,
            'label'   => 'Personnel Acronyms',
            'fields'  => ['acronym', 'account_manager'],
            'unique'  => ['acronym'],
            'headers' => ['Acronym', 'Account Manager'],
        ],
        'countries' => [
            'model'   => Country::class,
            'label'   => 'Countries / States',
            'fields'  => ['state', 'zone', 'country'],
            'unique'  => ['state', 'country'],
            'headers' => ['State', 'Zone', 'Country'],
        ],
        'tech_references' => [
            'model'   => TechReference::class,
            'label'   => 'Tech References',
            'fields'  => [
                'cliente_acronym',          // A - virtual → cliente_id
                'tech_reference',           // B
                'fecha_referencia',         // C
                'cp_numero',                // D
                'revision',                 // E
                'contacto',                 // F
                'estado_cliente',           // G
                'pais',                     // H
                'zona',                     // I
                'nombre_proyecto_cliente',  // J
                'core_business',            // K
                'pipe_in',                  // L
                'branch_in',               // M
                'descripcion_larga',        // N
                'amount_usd',               // O
                'amount_mxn',               // P
                'quotation_personnel',      // Q
                'account_manager',          // R
            ],
            'unique'  => ['tech_reference'],
            'headers' => ['Customer', 'Tech Ref', 'Date', 'CP', 'Rev', 'Contact', 'State', 'Country', 'Zone', 'Project Name', 'Core Business', 'Pipe (in)', 'Branch (in)', 'Description', 'Amount USD', 'Amount MXN', 'Personnel', 'Acct Mgr'],
            'lookups' => [
                'cliente_acronym' => [
                    'target' => 'cliente_id',
                    'model'  => Cliente::class,
                    'column' => 'alias',
                ],
            ],
            'display_fields'  => ['tech_reference', 'cp_numero', 'nombre_proyecto_cliente', 'amount_usd', 'account_manager'],
            'display_headers' => ['Tech Ref', 'CP', 'Project Name', 'Amount USD', 'Acct Mgr'],
        ],
        'opportunities' => [
            'model'   => Proyecto::class,
            'label'   => 'Oportunidades',
            'fields'  => [
                'cp_numero',                // A
                'cliente_acronym',          // B - virtual → cliente_id
                'contacto',                 // C
                'datos_contacto',           // D
                'lugar_nombre',             // E - virtual → lugar_id
                'alcance',                  // F
                'tech_reference',           // G
                'fecha_envio',              // H
                'fecha_modificacion_oferta',// I
                null,                       // J - Ofertas emitidas (omitir)
                'hitos_pago',               // K
                null,                       // L - Responsable (omitir)
                'estado',                   // M
                'archivo_oferta',           // N
                'concepto_adjudicacion',    // O
                'porcentaje_adjudicacion',  // P
                'ponderacion',              // Q
                'cartera_esperada',         // R
            ],
            'unique'  => ['cp_numero'],
            'headers' => ['CP', 'Cliente', 'Contacto', 'Datos Contacto', 'Lugar', 'Alcance', 'Tech Ref', 'Fecha Envío', 'Fecha Mod.', '— (omitir)', 'Hitos Pago', '— (omitir)', 'Estado', 'Archivo PDF', 'Concepto Adj.', '% Adj.', 'Ponderación', 'Cartera Esp.'],
            'lookups' => [
                'cliente_acronym' => [
                    'target' => 'cliente_id',
                    'model'  => Cliente::class,
                    'column' => 'alias',
                ],
                'lugar_nombre' => [
                    'target' => 'lugar_id',
                    'model'  => Lugar::class,
                    'column' => 'nombre',
                ],
            ],
            'display_fields'  => ['cp_numero', 'tech_reference', 'estado', 'monto_usd', 'cartera_esperada'],
            'display_headers' => ['CP', 'Oferta', 'Estado', 'Monto USD', 'Cartera'],
        ],
    ];

    // ──────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $catalogos = [];

        foreach (self::CATALOGOS as $slug => $config) {
            /** @var \Illuminate\Database\Eloquent\Model $modelClass */
            $modelClass = $config['model'];

            $catalogos[$slug] = [
                'label'          => $config['label'],
                // For the HTML table
                'fields'         => $config['display_fields'] ?? array_values(array_filter($config['fields'])),
                'headers'        => $config['display_headers'] ?? $config['headers'],
                // For the import modal column badges
                'import_headers' => $config['headers'],
                'records'        => $modelClass::orderBy('id')->get(),
            ];
        }

        return view('catalogos.index', compact('catalogos'));
    }

    // ──────────────────────────────────────────────────────────────────────────

    public function import(Request $request, string $catalog)
    {
        abort_unless(array_key_exists($catalog, self::CATALOGOS), 404);

        $request->validate([
            'archivo'     => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'fila_inicio' => 'required|integer|min:1|max:9999',
            'col_inicio'  => 'required|string|max:2',
        ]);

        $config     = self::CATALOGOS[$catalog];
        $modelClass = $config['model'];
        $fields     = $config['fields'];
        $unique     = $config['unique'];
        $lookups    = $config['lookups'] ?? [];

        // Convertir letra de columna a índice 0-base (A=0, B=1, AA=26…)
        $colStart = $this->colToIndex(strtoupper(trim($request->col_inicio)));
        $rowStart = (int) $request->fila_inicio;

        $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Reemplazar: truncar antes de insertar
        if ($request->boolean('reemplazar')) {
            $modelClass::truncate();
        }

        // Cache para FK lookups (evita N+1 queries por lookup repetido)
        $lookupCache = [];
        $insertados  = 0;
        $omitidos    = 0;

        foreach (array_slice($rows, $rowStart - 1) as $row) {
            $data = [];

            foreach ($fields as $i => $field) {
                // null = columna que se omite (no se guarda)
                if ($field === null) {
                    continue;
                }

                $val = trim((string) ($row[$colStart + $i] ?? ''));
                $val = $val !== '' ? $val : null;

                if (isset($lookups[$field])) {
                    // Campo virtual con lookup FK
                    $lookup   = $lookups[$field];
                    $cacheKey = $lookup['model'] . '|' . ($val ?? '');

                    if ($val !== null) {
                        if (!array_key_exists($cacheKey, $lookupCache)) {
                            $lookupCache[$cacheKey] = $lookup['model']::where($lookup['column'], $val)->value('id');
                        }
                        $data[$lookup['target']] = $lookupCache[$cacheKey];
                    } else {
                        $data[$lookup['target']] = null;
                    }
                } else {
                    $data[$field] = $val;
                }
            }

            // Fila completamente vacía → saltar
            if (count(array_filter($data, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            // Subset de campos únicos (usando los targets resueltos si aplica)
            $uniqueData = [];
            foreach ($unique as $u) {
                if (isset($lookups[$u])) {
                    $t = $lookups[$u]['target'];
                    $uniqueData[$t] = $data[$t] ?? null;
                } else {
                    $uniqueData[$u] = $data[$u] ?? null;
                }
            }

            // Si los campos únicos están todos vacíos → omitir
            if (count(array_filter($uniqueData, fn($v) => $v !== null && $v !== '')) === 0) {
                $omitidos++;
                continue;
            }

            $updateData = array_diff_key($data, $uniqueData);
            $modelClass::updateOrCreate($uniqueData, $updateData);
            $insertados++;
        }

        return redirect()
            ->route('catalogos.index')
            ->with('success', "«{$config['label']}»: {$insertados} registros importados, {$omitidos} omitidos.");
    }

    // ──────────────────────────────────────────────────────────────────────────

    /** Convierte letra(s) de columna Excel a índice 0-base: A→0, B→1, Z→25, AA→26. */
    private function colToIndex(string $col): int
    {
        $index = 0;
        foreach (str_split($col) as $char) {
            $index = $index * 26 + (ord($char) - ord('A') + 1);
        }
        return $index - 1;
    }
}
