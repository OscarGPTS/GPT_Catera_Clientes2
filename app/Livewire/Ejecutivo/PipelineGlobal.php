<?php

namespace App\Livewire\Ejecutivo;

use Livewire\Component;

class PipelineGlobal extends Component
{
    public function getStatusOfertasProperty(): array
    {
        $months = [
            ['year' => 2025, 'num' => 11, 'label' => 'Nov'],
            ['year' => 2025, 'num' => 12, 'label' => 'Dic'],
            ['year' => 2026, 'num' => 1,  'label' => 'Ene'],
            ['year' => 2026, 'num' => 2,  'label' => 'Feb'],
            ['year' => 2026, 'num' => 3,  'label' => 'Mar'],
            ['year' => 2026, 'num' => 4,  'label' => 'Abr'],
            ['year' => 2026, 'num' => 5,  'label' => 'May'],
        ];

        $proyectos = [
            ['nombre' => 'IGASAMEX HTS 30x4" Oleofinos',           'cp' => '152/25', 'monto' => 37000,      'probs' => [75,  75, 100, 100, 100, 100, 100], 'resp' => 'Diego R',    'sublinea' => 'HTS',  'alias' => 'IGX'],
            ['nombre' => 'ENGIE HTP 42x24" VDR',                  'cp' => '157/25', 'monto' => 933000,      'probs' => [null, null, null, 25,  25,  50,  50], 'resp' => 'Kevin P',    'sublinea' => 'HTP',  'alias' => 'ENG'],
            ['nombre' => 'PIR SYSTEM HT 30x20" Cactus',            'cp' => '1',      'monto' => 103000,      'probs' => [null, null, 25,  25,  50,  25,   0], 'resp' => 'Diego R',    'sublinea' => 'HTS',  'alias' => 'PIR'],
            ['nombre' => 'NATURGY Anillos separadores',            'cp' => '2',      'monto' => 64000,       'probs' => [null, null, 25,  50,  75,  75, 100], 'resp' => 'Diego R',    'sublinea' => 'VRS',  'alias' => 'NAT'],
            ['nombre' => 'IGASAMEX VCP Dif. Diámetros',            'cp' => '3',      'monto' => 29000,       'probs' => [null, null, 25,  25,  50,  50,  75], 'resp' => 'Diego R',    'sublinea' => 'VLV',  'alias' => 'IGX'],
            ['nombre' => 'PROTEXA Válvulas Cluster SEJKAN',       'cp' => '4',      'monto' => 1721000,     'probs' => [null, null, null, 25,  25,  25,  50], 'resp' => 'Diego R',    'sublinea' => 'VLV',  'alias' => 'PTX'],
            ['nombre' => 'EUROINOVA DLS 6" 600#',                 'cp' => '5',      'monto' => 121000,      'probs' => [null, null, 25,  25,  25,  50,  50], 'resp' => 'Diego R',    'sublinea' => 'DLSS', 'alias' => 'EIN'],
            ['nombre' => 'MOLPER DLS 6" 600# Hidalgo',             'cp' => '16',     'monto' => 2977000,     'probs' => [null, null, 25,  50,  50,  75, 100], 'resp' => 'Aquiles G',  'sublinea' => 'DLSS', 'alias' => 'MOL'],
            ['nombre' => 'SERPORT HTP 24x16" Submarino',           'cp' => '6',      'monto' => 352000,      'probs' => [null, null, 25,  25,  25,  25,  50], 'resp' => 'Sergio O',   'sublinea' => 'HTP',  'alias' => 'SRP'],
            ['nombre' => 'GCI HT 8x8" Nafta',                     'cp' => '7',      'monto' => 9000,        'probs' => [null, null, 25,  50,  75,  75, 100], 'resp' => 'Diego R',    'sublinea' => 'HTS',  'alias' => 'GCI'],
            ['nombre' => 'ICA HTSF 24x24" Naucalpan',              'cp' => '8',      'monto' => 1045000,     'probs' => [null, null, 25,  50,  75, 100, 100], 'resp' => 'Diego R',    'sublinea' => 'HTSF', 'alias' => 'ICA'],
            ['nombre' => 'SICIM HT 30x20 600# Ags',               'cp' => '9',      'monto' => 256000,      'probs' => [null, null, null, 25,  25,  50,  50], 'resp' => 'Kevin P',    'sublinea' => 'HTS',  'alias' => 'SIC'],
            ['nombre' => 'COPC Juntas dieléctricas',               'cp' => '10',     'monto' => 5000,        'probs' => [null, null, 25,  50,  75, 100, 100], 'resp' => 'Kevin P',    'sublinea' => 'OTH',  'alias' => 'CPC'],
            ['nombre' => 'INDHECA Separador Horiz. Bakte',         'cp' => '-',      'monto' => 376000,      'probs' => [null, null, null, 25,  50,  50,  75], 'resp' => 'Guadalupe O','sublinea' => 'P&C',  'alias' => 'IGC'],
            ['nombre' => 'SARREAL Drillings 2" Niple',             'cp' => '-',      'monto' => 45000,       'probs' => [null, null, null, 25,  50,  75, 100], 'resp' => 'Sergio O',   'sublinea' => 'HTP',  'alias' => 'SAR'],
            ['nombre' => 'ARSEAL Válvulas Trunnion 8y10',          'cp' => '11',     'monto' => 69000,       'probs' => [null, null, null, 25,  25,  25,  25], 'resp' => 'Kevin P',    'sublinea' => 'VLV',  'alias' => 'ARS'],
            ['nombre' => 'ESENTIA DLSS 36" Villa de Reyes',       'cp' => '12',     'monto' => 1260000,     'probs' => [null, null, null, 25,  50,  75,  75], 'resp' => 'Aquiles G',  'sublinea' => 'DLSS', 'alias' => 'ESE'],
            ['nombre' => 'ESENTIA HTP 8" y 2" Samalayuca',         'cp' => '13',     'monto' => 63000,       'probs' => [null, null, null, 25,  25,  50,  75], 'resp' => 'Aquiles G',  'sublinea' => 'HTP',  'alias' => 'ESE'],
            ['nombre' => 'SEDENA Frente 10 Tren Mx-Qro',          'cp' => '14',     'monto' => 8892000,     'probs' => [null, null, null, 25,  50,  50,  75], 'resp' => 'Aquiles G',  'sublinea' => 'HTSF', 'alias' => 'SDN'],
            ['nombre' => 'SEDENA Frente 11 Tren Mx-Qro',          'cp' => '15',     'monto' => 36375000,    'probs' => [null, null, null, 25,  25,  50,  75], 'resp' => 'Aquiles G',  'sublinea' => 'HTSF', 'alias' => 'SDN'],
        ];

        return [
            'months'    => $months,
            'proyectos' => $proyectos,
        ];
    }

    public function getKpisProperty(): array
    {
        $data = $this->statusOfertas['proyectos'];
        $total = 0;
        $ponderado = 0;
        foreach ($data as $p) {
            $total += $p['monto'];
            $lp = 0;
            for ($i = 6; $i >= 0; $i--) {
                if ($p['probs'][$i] !== null) { $lp = $p['probs'][$i]; break; }
            }
            $ponderado += $p['monto'] * $lp / 100;
        }
        return [
            'count'     => count($data),
            'total'     => $total,
            'ponderado' => $ponderado,
        ];
    }

    public function render()
    {
        return view('livewire.ejecutivo.pipeline-global', [
            'statusOfertas' => $this->statusOfertas,
            'kpis'         => $this->kpis,
        ])->layout('components.layouts.app', ['fullWidth' => true]);
    }
}