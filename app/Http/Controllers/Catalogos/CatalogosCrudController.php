<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Cliente;
use App\Models\CoreBusiness;
use App\Models\Country;
use App\Models\Customer;
use App\Models\PersonnelAcronym;
use App\Models\Ponderacion;
use App\Models\Size;
use App\Models\TechReference;
use App\Models\User;
use App\Models\Vario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogosCrudController extends Controller
{
    /**
     * Definición de cada catálogo administrable.
     *
     * model:           clase Eloquent
     * label:           título visible del tab
     * fields:          columnas a mostrar en la tabla y campos del formulario
     * validation:      reglas base (no incluye unique con ignore; se aplica en cada acción)
     * form:            esquema de cada campo del formulario [name, label, type, required, options(callable)?]
     */
    public const CATALOGOS = [
        'ponderaciones' => [
            'model' => Ponderacion::class,
            'label' => 'Ponderaciones',
        ],
        'tech_references' => [
            'model' => TechReference::class,
            'label' => 'Tech References',
        ],
        'customers' => [
            'model' => Customer::class,
            'label' => 'Customers',
        ],
        'core_businesses' => [
            'model' => CoreBusiness::class,
            'label' => 'Core Business',
        ],
        'personnel_acronyms' => [
            'model' => PersonnelAcronym::class,
            'label' => 'Personnel Acronyms',
        ],
        'countries' => [
            'model' => Country::class,
            'label' => 'Countries / States',
        ],
        'varios' => [
            'model' => Vario::class,
            'label' => 'Varios',
        ],
        'sizes' => [
            'model' => Size::class,
            'label' => 'Sizes',
        ],
    ];

    public function index()
    {
        $catalogos = [];

        foreach (self::CATALOGOS as $slug => $config) {
            /** @var \Illuminate\Database\Eloquent\Model $modelClass */
            $modelClass = $config['model'];

            $records = $modelClass::where('status', true);

            // Eager-load para tech_references y personnel_acronyms
            if ($slug === 'tech_references') {
                $records = $records->with('cliente:id,alias,razon_social');
            } elseif ($slug === 'personnel_acronyms') {
                $records = $records->with('user:id,name,email');
            }

            $catalogos[$slug] = [
                'label'   => $config['label'],
                'columns' => $this->columns($slug),
                'form'    => $this->formSchema($slug),
                'records' => $records->orderBy('id')->get(),
            ];
        }

        return view('catalogos.crud', [
            'catalogos' => $catalogos,
            'tabs'      => array_map(fn($slug, $c) => ['slug' => $slug, 'label' => $c['label']], array_keys(self::CATALOGOS), self::CATALOGOS),
        ]);
    }

    public function store(Request $request, string $catalog)
    {
        abort_unless(isset(self::CATALOGOS[$catalog]), 404);

        $modelClass = self::CATALOGOS[$catalog]['model'];
        $data = $request->validate($this->rules($catalog));
        $data['status'] = true;

        $modelClass::create($data);

        return redirect()
            ->route('catalogos.crud.index', ['tab' => $catalog])
            ->with('success', 'Registro creado correctamente.');
    }

    public function update(Request $request, string $catalog, int $id)
    {
        abort_unless(isset(self::CATALOGOS[$catalog]), 404);

        $modelClass = self::CATALOGOS[$catalog]['model'];
        $record = $modelClass::findOrFail($id);

        $data = $request->validate($this->rules($catalog, $id));
        $record->update($data);

        return redirect()
            ->route('catalogos.crud.index', ['tab' => $catalog])
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(string $catalog, int $id)
    {
        abort_unless(isset(self::CATALOGOS[$catalog]), 404);

        $modelClass = self::CATALOGOS[$catalog]['model'];
        $record = $modelClass::findOrFail($id);

        // Soft-delete lógico vía status=false (preserva relaciones existentes)
        $record->update(['status' => false]);

        return redirect()
            ->route('catalogos.crud.index', ['tab' => $catalog])
            ->with('success', 'Registro eliminado correctamente.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Definiciones por catálogo
    // ──────────────────────────────────────────────────────────────────────────

    private function columns(string $slug): array
    {
        return match ($slug) {
            'ponderaciones' => [
                ['key' => 'orden',      'label' => 'Orden'],
                ['key' => 'concepto',   'label' => 'Concepto'],
                ['key' => 'porcentaje', 'label' => '%'],
                ['key' => 'color',      'label' => 'Color'],
            ],
            'tech_references' => [
                ['key' => 'tech_reference',          'label' => 'Tech Ref'],
                ['key' => 'cp_numero',               'label' => 'CP'],
                ['key' => 'cliente.alias',           'label' => 'Cliente'],
                ['key' => 'nombre_proyecto_cliente', 'label' => 'Proyecto'],
                ['key' => 'core_business',           'label' => 'Core Bus.'],
                ['key' => 'amount_usd',              'label' => 'USD'],
                ['key' => 'account_manager',         'label' => 'Acct Mgr'],
            ],
            'customers' => [
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'acronym',  'label' => 'Acrónimo'],
            ],
            'core_businesses' => [
                ['key' => 'core_business', 'label' => 'Core Business'],
                ['key' => 'acronym',       'label' => 'Acrónimo'],
                ['key' => 'description',   'label' => 'Descripción'],
            ],
            'personnel_acronyms' => [
                ['key' => 'acronym',         'label' => 'Acrónimo'],
                ['key' => 'account_manager', 'label' => 'Account Manager'],
                ['key' => 'user.name',       'label' => 'Usuario'],
            ],
            'countries' => [
                ['key' => 'state',   'label' => 'Estado'],
                ['key' => 'zone',    'label' => 'Zona'],
                ['key' => 'country', 'label' => 'País'],
            ],
            'varios' => [
                ['key' => 'nombre', 'label' => 'Nombre'],
            ],
            'sizes' => [
                ['key' => 'size_principal',  'label' => 'Size Principal (in)'],
                ['key' => 'size_secundario', 'label' => 'Size Secundario'],
            ],
        };
    }

    private function formSchema(string $slug): array
    {
        $colores = ['red' => 'Rojo', 'amber' => 'Ámbar', 'yellow' => 'Amarillo', 'green' => 'Verde', 'emerald' => 'Esmeralda', 'cyan' => 'Cian', 'blue' => 'Azul', 'indigo' => 'Índigo', 'purple' => 'Morado', 'slate' => 'Gris'];

        return match ($slug) {
            'ponderaciones' => [
                ['name' => 'concepto',   'label' => 'Concepto',  'type' => 'text',   'required' => true],
                ['name' => 'porcentaje', 'label' => '% (0-100)', 'type' => 'number', 'required' => true],
                ['name' => 'orden',      'label' => 'Orden',     'type' => 'number'],
                ['name' => 'color',      'label' => 'Color',     'type' => 'select', 'options' => $colores],
            ],
            'tech_references' => [
                ['name' => 'tech_reference',           'label' => 'Tech Reference', 'type' => 'text',     'required' => true],
                ['name' => 'cliente_id',               'label' => 'Cliente',        'type' => 'select',   'options' => Cliente::orderBy('razon_social')->pluck('razon_social', 'id')->all()],
                ['name' => 'cp_numero',                'label' => 'CP',             'type' => 'text'],
                ['name' => 'fecha_referencia',         'label' => 'Fecha (YYMMDD)', 'type' => 'text'],
                ['name' => 'revision',                 'label' => 'Revisión',       'type' => 'number'],
                ['name' => 'contacto',                 'label' => 'Contacto',       'type' => 'text'],
                ['name' => 'estado_cliente',           'label' => 'Estado',         'type' => 'text'],
                ['name' => 'pais',                     'label' => 'País',           'type' => 'text'],
                ['name' => 'zona',                     'label' => 'Zona',           'type' => 'text'],
                ['name' => 'nombre_proyecto_cliente',  'label' => 'Proyecto cliente', 'type' => 'text'],
                ['name' => 'core_business',            'label' => 'Core Business',  'type' => 'text'],
                ['name' => 'pipe_in',                  'label' => 'Pipe (in)',      'type' => 'number',   'step' => '0.01'],
                ['name' => 'branch_in',                'label' => 'Branch (in)',    'type' => 'number',   'step' => '0.01'],
                ['name' => 'descripcion_larga',        'label' => 'Descripción',    'type' => 'textarea'],
                ['name' => 'amount_usd',               'label' => 'Amount USD',     'type' => 'number',   'step' => '0.01'],
                ['name' => 'amount_mxn',               'label' => 'Amount MXN',     'type' => 'number',   'step' => '0.01'],
                ['name' => 'quotation_personnel',      'label' => 'Personnel',      'type' => 'text'],
                ['name' => 'account_manager',          'label' => 'Account Manager','type' => 'text'],
            ],
            'customers' => [
                ['name' => 'customer', 'label' => 'Customer', 'type' => 'text', 'required' => true],
                ['name' => 'acronym',  'label' => 'Acrónimo', 'type' => 'text'],
            ],
            'core_businesses' => [
                ['name' => 'core_business', 'label' => 'Core Business', 'type' => 'text', 'required' => true],
                ['name' => 'acronym',       'label' => 'Acrónimo',      'type' => 'text'],
                ['name' => 'description',   'label' => 'Descripción',   'type' => 'textarea'],
            ],
            'personnel_acronyms' => [
                ['name' => 'acronym',         'label' => 'Acrónimo',        'type' => 'text', 'required' => true],
                ['name' => 'account_manager', 'label' => 'Account Manager', 'type' => 'text'],
                ['name' => 'user_id',         'label' => 'Usuario',         'type' => 'select', 'options' => User::orderBy('name')->pluck('name', 'id')->all()],
            ],
            'countries' => [
                ['name' => 'state',   'label' => 'Estado', 'type' => 'text', 'required' => true],
                ['name' => 'zone',    'label' => 'Zona',   'type' => 'text'],
                ['name' => 'country', 'label' => 'País',   'type' => 'text', 'required' => true],
            ],
            'varios' => [
                ['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
            ],
            'sizes' => [
                ['name' => 'size_principal',  'label' => 'Size Principal (in)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                ['name' => 'size_secundario', 'label' => 'Size Secundario',     'type' => 'text'],
            ],
        };
    }

    private function rules(string $slug, ?int $ignoreId = null): array
    {
        return match ($slug) {
            'ponderaciones' => [
                'concepto'   => ['required', 'string', 'max:100', Rule::unique('ponderaciones', 'concepto')->ignore($ignoreId)],
                'porcentaje' => 'required|integer|min:0|max:100',
                'orden'      => 'nullable|integer|min:0|max:255',
                'color'      => 'nullable|string|max:32',
            ],
            'tech_references' => [
                'tech_reference'          => ['required', 'string', 'max:255', Rule::unique('tech_references', 'tech_reference')->ignore($ignoreId)],
                'cliente_id'              => 'nullable|integer|exists:clientes,id',
                'cp_numero'               => 'nullable|string|max:255',
                'fecha_referencia'        => 'nullable|string|max:6',
                'revision'                => 'nullable|integer|min:0|max:255',
                'contacto'                => 'nullable|string|max:255',
                'estado_cliente'          => 'nullable|string|max:255',
                'pais'                    => 'nullable|string|max:10',
                'zona'                    => 'nullable|string|max:255',
                'nombre_proyecto_cliente' => 'nullable|string|max:255',
                'core_business'           => 'nullable|string|max:255',
                'pipe_in'                 => 'nullable|numeric',
                'branch_in'               => 'nullable|numeric',
                'descripcion_larga'       => 'nullable|string',
                'amount_usd'              => 'nullable|numeric',
                'amount_mxn'              => 'nullable|numeric',
                'quotation_personnel'     => 'nullable|string|max:255',
                'account_manager'         => 'nullable|string|max:255',
            ],
            'customers' => [
                'customer' => 'required|string|max:255',
                'acronym'  => ['nullable', 'string', 'max:50', Rule::unique('customers', 'acronym')->ignore($ignoreId)],
            ],
            'core_businesses' => [
                'core_business' => 'required|string|max:255',
                'acronym'       => ['nullable', 'string', 'max:50', Rule::unique('core_businesses', 'acronym')->ignore($ignoreId)],
                'description'   => 'nullable|string',
            ],
            'personnel_acronyms' => [
                'acronym'         => ['required', 'string', 'max:50', Rule::unique('personnel_acronyms', 'acronym')->ignore($ignoreId)],
                'account_manager' => 'nullable|string|max:50',
                'user_id'         => 'nullable|integer|exists:users,id',
            ],
            'countries' => [
                'state'   => 'required|string|max:255',
                'zone'    => 'nullable|string|max:255',
                'country' => 'required|string|max:10',
            ],
            'varios' => [
                'nombre' => ['required', 'string', 'max:255', Rule::unique('varios', 'nombre')->ignore($ignoreId)],
            ],
            'sizes' => [
                'size_principal'  => 'required|numeric',
                'size_secundario' => 'nullable|string|max:50',
            ],
        };
    }
}
