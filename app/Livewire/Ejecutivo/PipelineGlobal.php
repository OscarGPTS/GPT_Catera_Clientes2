<?php

namespace App\Livewire\Ejecutivo;

use App\Models\Proyectos\Proyecto;
use Livewire\Component;

class PipelineGlobal extends Component
{
    /** @return \Illuminate\Support\Collection */
    public function getPipelineProperty()
    {
        return Proyecto::with(['cliente', 'sublinea', 'cotizaciones'])
            ->orderByDesc('ponderacion')
            ->orderBy('created_at')
            ->get()
            ->map(function (Proyecto $p) {
                $cotizacion = $p->cotizaciones->sortByDesc('version')->first();
                $monto      = (float) ($cotizacion?->precio_venta_final ?? 0);
                $fecha      = $cotizacion?->fecha_emision;
                $mes        = $fecha ? (int) $fecha->format('n') : null;
                // Offers from 2025 still active in 2026 → show in month 11 (Nov) of the timeline
                $mesYear    = $fecha ? (int) $fecha->format('Y') : null;
                if ($mesYear && $mesYear < 2026) {
                    $mes = (int) $fecha->format('n'); // keep original month (Nov=11, Dec=12)
                }

                return [
                    'id'          => $p->id,
                    'cp'          => $p->cp_numero,
                    'cliente'     => $p->cliente?->razon_social ?? 'N/A',
                    'alias'       => $p->cliente?->alias_3letras ?? '---',
                    'sublinea'    => $p->sublinea?->codigo ?? '---',
                    'sector'      => $p->sector,
                    'estado'      => $p->estado,
                    'ponderacion' => (int) $p->ponderacion,
                    'monto'       => $monto,
                    'mes'         => $mes,
                    'mes_year'    => $mesYear,
                    'fecha_str'   => $fecha ? $fecha->format('d/m/Y') : '—',
                ];
            });
    }

    /** KPI totals */
    public function getKpisProperty(): array
    {
        $data = $this->pipeline;

        return [
            'total'     => $data->sum('monto'),
            'ponderado' => $data->sum(fn ($p) => $p['monto'] * $p['ponderacion'] / 100),
            'count'     => $data->count(),
        ];
    }

    public function render()
    {
        return view('livewire.ejecutivo.pipeline-global', [
            'pipeline' => $this->pipeline,
            'kpis'     => $this->kpis,
        ])->layout('components.layouts.app', ['fullWidth' => true]);
    }
}
