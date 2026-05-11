<?php

namespace App\Livewire\Finanzas;

use App\Services\Finanzas\CierreService;
use Livewire\Component;

class CierresIndex extends Component
{
    public $anio;
    public $mes;
    public $tab = 'sat';
    public $generando = false;
    public $progresoEtapa = 0;
    public $progresoTexto = '';
    public $progresoPorcentaje = 0;

    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->anio = now()->year;
        $this->mes = now()->month;
    }

    public function generarCierre()
    {
        $this->authorize('generar cierre gerencial');

        $this->generando = true;
        $this->progresoEtapa = 1;
        $this->progresoTexto = 'Calculando SAT base...';
        $this->progresoPorcentaje = 33;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            $service = new CierreService();
            $service->generarCierreGerencial((int) $this->mes, (int) $this->anio, auth()->id());

            $this->progresoEtapa = 2;
            $this->progresoTexto = 'Calculando Devengado...';
            $this->progresoPorcentaje = 66;

            $this->progresoEtapa = 3;
            $this->progresoTexto = 'Calculando Pipeline ponderado...';
            $this->progresoPorcentaje = 100;

            $this->successMessage = 'Cierre generado correctamente para ' . $this->mesNombre() . ' ' . $this->anio . '.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al generar el cierre: ' . $e->getMessage();
        }

        $this->generando = false;
        $this->progresoEtapa = 0;
        $this->progresoTexto = '';
        $this->progresoPorcentaje = 0;
    }

    public function aprobarCierre()
    {
        $this->authorize('aprobar cierre');

        try {
            $service = new CierreService();
            $cierre = \App\Models\Finanzas\CierreMensual::where('mes', $this->mes)
                ->where('anio', $this->anio)
                ->where('tipo', 'gerencial_avance')
                ->firstOrFail();

            $service->aprobarCierre($cierre->id, auth()->id());
            $this->successMessage = 'Cierre aprobado y bloqueado correctamente.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al aprobar el cierre: ' . $e->getMessage();
        }
    }

    public function getCierreDataProperty()
    {
        $service = new CierreService();
        return $service->getCierreData((int) $this->mes, (int) $this->anio);
    }

    public function getAniosDisponiblesProperty()
    {
        return range(now()->year, now()->year - 4);
    }

    public function getMesesProperty()
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    public function render()
    {
        $data = $this->cierreData;

        return view('livewire.finanzas.cierres-index', [
            'aniosDisponibles' => $this->aniosDisponibles,
            'meses' => $this->meses,
            'facturasSat' => $data['facturas_sat'],
            'totalSat' => $data['total_sat'],
            'proyectosDevengado' => $data['proyectos_devengado'],
            'pipelinePonderado' => $data['pipeline_ponderado'],
            'totalDevengado' => $data['total_devengado'],
            'totalPipeline' => $data['total_pipeline'],
            'totalGerencial' => $data['total_gerencial'],
            'delta' => $data['delta'],
            'deltaPct' => $data['delta_pct'],
            'workflowStatus' => $data['workflow_status'],
            'generadoPor' => $data['generado_por'],
            'fechaGeneracion' => $data['fecha_generacion'],
            'revisadoCfo' => $data['revisado_cfo'],
            'aprobadoDg' => $data['aprobado_dg'],
            'auditEntries' => $data['audit_entries'],
        ])->layout('components.layouts.app');
    }

    protected function mesNombre(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $meses[(int) $this->mes] ?? '';
    }
}