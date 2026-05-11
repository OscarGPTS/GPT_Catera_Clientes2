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

    /** Data prepared for Chart.js */
    public function getChartDataProperty(): array
    {
        $pipeline = $this->pipeline;

        $monthDefs = [
            ['year' => 2025, 'num' => 11, 'label' => 'Nov 25'],
            ['year' => 2025, 'num' => 12, 'label' => 'Dic 25'],
            ['year' => 2026, 'num' => 1,  'label' => 'Ene 26'],
            ['year' => 2026, 'num' => 2,  'label' => 'Feb 26'],
            ['year' => 2026, 'num' => 3,  'label' => 'Mar 26'],
            ['year' => 2026, 'num' => 4,  'label' => 'Abr 26'],
            ['year' => 2026, 'num' => 5,  'label' => 'May 26'],
            ['year' => 2026, 'num' => 6,  'label' => 'Jun 26'],
            ['year' => 2026, 'num' => 7,  'label' => 'Jul 26'],
            ['year' => 2026, 'num' => 8,  'label' => 'Ago 26'],
            ['year' => 2026, 'num' => 9,  'label' => 'Sep 26'],
            ['year' => 2026, 'num' => 10, 'label' => 'Oct 26'],
            ['year' => 2026, 'num' => 11, 'label' => 'Nov 26'],
            ['year' => 2026, 'num' => 12, 'label' => 'Dic 26'],
        ];

        // Stacked bar: amount (millions) per month per ponderacion band
        $byMonth = [
            'p100' => array_fill(0, 14, 0),
            'p75'  => array_fill(0, 14, 0),
            'p50'  => array_fill(0, 14, 0),
            'p25'  => array_fill(0, 14, 0),
            'p10'  => array_fill(0, 14, 0),
        ];

        foreach ($pipeline as $p) {
            $colIdx = 2; // fallback: Ene 26
            foreach ($monthDefs as $idx => $m) {
                if ($p['mes'] == $m['num'] && $p['mes_year'] == $m['year']) {
                    $colIdx = $idx;
                    break;
                }
            }
            $key = 'p' . $p['ponderacion'];
            if (isset($byMonth[$key])) {
                $byMonth[$key][$colIdx] = round($byMonth[$key][$colIdx] + ($p['monto'] / 1_000_000), 3);
            }
        }

        // Horizontal bar: amount (millions) per sublinea, sorted desc
        $bySublinea = $pipeline
            ->groupBy('sublinea')
            ->map(fn ($g) => round($g->sum('monto') / 1_000_000, 3))
            ->sortDesc()
            ->take(10)
            ->toArray();

        // Horizontal bar: amount (millions) per top client, sorted desc
        $byCliente = $pipeline
            ->groupBy('alias')
            ->map(fn ($g) => round($g->sum('monto') / 1_000_000, 3))
            ->sortDesc()
            ->take(8)
            ->toArray();

        return [
            'monthLabels' => array_column($monthDefs, 'label'),
            'byMonth'     => $byMonth,
            'bySublinea'  => $bySublinea,
            'byCliente'   => $byCliente,
        ];
    }

    public function render()
    {
        return view('livewire.ejecutivo.pipeline-global', [
            'pipeline'  => $this->pipeline,
            'kpis'      => $this->kpis,
            'chartData' => $this->chartData,
        ])->layout('components.layouts.app', ['fullWidth' => true]);
    }
}
