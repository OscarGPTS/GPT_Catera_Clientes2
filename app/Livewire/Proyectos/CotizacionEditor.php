<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\CotizacionPartida;
use App\Services\Cotizaciones\CalculadoraCoss;
use Livewire\Component;

class CotizacionEditor extends Component
{
    public Proyecto $proyecto;
    public $cotizacionActiva = null;
    public $cotizaciones = [];
    public $selectedVersion = null;

    public $activeSection = 'partidas';
    public $partidas = [];
    public $factores = [
        'indirectos' => 10,
        'admin' => 12,
        'utilidad' => 25,
    ];
    public $condiciones = [
        'anticipo' => 50,
        'contraEntrega' => 50,
        'validez' => 30,
        'tiempoEntrega' => 60,
        'unidadTiempo' => 'dias',
        'notas' => '',
    ];
    public $ajusteManual = 0;
    public $moneda = 'USD';
    public $successMessage = '';

    public function mount(Proyecto $proyecto)
    {
        $this->proyecto = $proyecto;
        $this->proyecto->load(['cliente', 'sublinea', 'cotizaciones.partidas']);
        $this->cotizaciones = $this->proyecto->cotizaciones->sortByDesc('version')->values();

        if ($this->cotizaciones->count() > 0) {
            $this->cotizacionActiva = $this->cotizaciones->first();
            $this->selectedVersion = $this->cotizacionActiva->id;
            $this->loadCotizacion($this->cotizacionActiva);
        }
    }

    public function loadCotizacion($cotizacion)
    {
        $this->partidas = $cotizacion->partidas->map(fn($p) => [
            'id' => $p->id,
            'numero' => 'P-' . str_pad($p->numero_partida, 2, '0', STR_PAD_LEFT),
            'descripcion' => $p->descripcion,
            'cantidad' => (float) $p->cantidad,
            'unidad' => $p->unidad ?? 'pza',
            'costoUnitario' => (float) $p->costo_unitario,
        ])->values()->toArray();

        $this->factores = [
            'indirectos' => (float) ($cotizacion->factor_indirectos * 100),
            'admin' => (float) ($cotizacion->factor_admin * 100),
            'utilidad' => (float) ($cotizacion->factor_utilidad * 100),
        ];

        $this->moneda = $cotizacion->moneda;
        $this->ajusteManual = (float) $cotizacion->precio_venta_final - (float) $cotizacion->precio_venta_calculado;
    }

    public function changeVersion($cotizacionId)
    {
        $cotizacion = Cotizacion::with('partidas')->find($cotizacionId);
        if ($cotizacion && $cotizacion->proyecto_id === $this->proyecto->id) {
            $this->cotizacionActiva = $cotizacion;
            $this->selectedVersion = $cotizacion->id;
            $this->loadCotizacion($cotizacion);
        }
    }

    public function agregarPartida()
    {
        $this->partidas[] = [
            'id' => null,
            'numero' => 'P-' . str_pad(count($this->partidas) + 1, 2, '0', STR_PAD_LEFT),
            'descripcion' => '',
            'cantidad' => 1,
            'unidad' => 'pza',
            'costoUnitario' => 0,
        ];
    }

    public function removerPartida($index)
    {
        unset($this->partidas[$index]);
        $this->partidas = array_values($this->partidas);
        foreach ($this->partidas as $i => &$p) {
            $p['numero'] = 'P-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        }
    }

    public function getCostoDirectoProperty()
    {
        return collect($this->partidas)->sum(fn($p) => ($p['cantidad'] ?? 0) * ($p['costoUnitario'] ?? 0));
    }

    public function getSubtotalProperty()
    {
        return $this->costoDirecto * (1 + ($this->factores['indirectos'] ?? 0) / 100);
    }

    public function getSubtotalAdminProperty()
    {
        return $this->subtotal * (1 + ($this->factores['admin'] ?? 0) / 100);
    }

    public function getPrecioCalculadoProperty()
    {
        return $this->subtotalAdmin * (1 + ($this->factores['utilidad'] ?? 0) / 100);
    }

    public function getPrecioFinalProperty()
    {
        return $this->precioCalculado + (float) ($this->ajusteManual ?? 0);
    }

    public function getMargenNetoProperty()
    {
        $pf = $this->precioFinal;
        $cd = $this->costoDirecto;
        if ($pf == 0 || $cd == 0) {
            return 0;
        }
        return (($pf - $cd) / $pf) * 100;
    }

    public function guardarBorrador()
    {
        $this->saveCotizacion('borrador');
    }

    public function enviarRevision()
    {
        $this->saveCotizacion('revision');
    }

    protected function saveCotizacion(string $status)
    {
        $calculadora = new CalculadoraCoss();
        $resultado = $calculadora->calcular(
            costoDirecto: $this->costoDirecto,
            factorIndirectos: ($this->factores['indirectos'] ?? 0) / 100,
            factorAdmin: ($this->factores['admin'] ?? 0) / 100,
            factorUtilidad: ($this->factores['utilidad'] ?? 0) / 100,
        );

        $ultimaVersion = $this->proyecto->cotizaciones()->max('version') ?? 0;
        $nuevaVersion = $ultimaVersion + 1;

        $cotizacion = Cotizacion::create([
            'proyecto_id' => $this->proyecto->id,
            'version' => $nuevaVersion,
            'costo_directo' => $resultado->costoDirecto,
            'factor_indirectos' => ($this->factores['indirectos'] ?? 0) / 100,
            'factor_admin' => ($this->factores['admin'] ?? 0) / 100,
            'factor_utilidad' => ($this->factores['utilidad'] ?? 0) / 100,
            'precio_venta_calculado' => $resultado->precioVenta,
            'precio_venta_final' => $this->precioFinal,
            'moneda' => $this->moneda,
            'status' => $status,
            'generado_por' => auth()->id(),
            'observaciones' => $this->condiciones['notas'] ?? null,
        ]);

        foreach ($this->partidas as $i => $p) {
            if (empty($p['descripcion'])) {
                continue;
            }
            CotizacionPartida::create([
                'cotizacion_id' => $cotizacion->id,
                'numero_partida' => $i + 1,
                'descripcion' => $p['descripcion'],
                'cantidad' => $p['cantidad'] ?? 0,
                'unidad' => $p['unidad'] ?? 'pza',
                'costo_unitario' => $p['costoUnitario'] ?? 0,
                'costo_total' => ($p['cantidad'] ?? 0) * ($p['costoUnitario'] ?? 0),
            ]);
        }

        $this->proyecto->load('cotizaciones.partidas');
        $this->cotizaciones = $this->proyecto->cotizaciones->sortByDesc('version')->values();
        $this->cotizacionActiva = $cotizacion;
        $this->selectedVersion = $cotizacion->id;
        $this->loadCotizacion($cotizacion);

        $this->successMessage = $status === 'borrador'
            ? "Cotización v{$nuevaVersion} guardada como borrador."
            : "Cotización v{$nuevaVersion} enviada a revisión.";
    }

    public function render()
    {
        return view('livewire.proyectos.cotizacion-editor')->layout('components.layouts.app');
    }
}