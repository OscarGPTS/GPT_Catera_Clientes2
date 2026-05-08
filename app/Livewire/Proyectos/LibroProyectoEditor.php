<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\LibroProyecto;
use App\Models\Proyectos\LibroSeccion;
use App\Models\Proyectos\LibroSeccionChecklist;
use App\Services\Libro\AperturaLibroService;
use App\Services\Libro\AvanceCalculator;
use Livewire\Component;

class LibroProyectoEditor extends Component
{
    public Proyecto $proyecto;
    public $secciones = [];
    public $porcentajeGlobal = 0;
    public $itemsCompletados = 0;
    public $itemsTotales = 0;
    public $successMessage = '';

    protected $checklistPlantillas = [
        'A' => ['Programa general de obra (Gantt)', 'Ruta crítica definida', 'Hitos contractuales identificados', 'Calendario de recursos', 'Plan de adquisiciones'],
        'B' => ['Planos de detalle aprobados', 'Memoria de cálculo', 'Especificaciones técnicas', 'Modelo 3D / BIM', 'Listado de materiales (BOM)', 'Diagramas P&ID'],
        'C' => ['Licencia de construcción', 'Permiso ambiental', 'Factibilidad de servicios', 'Permiso de vialidad', 'Dictamen de protección civil'],
        'D' => ['Estudio de mecánica de suelos', 'Estudio topográfico', 'Estudio de impacto ambiental', 'Dictamen estructural', 'Estudio hidrológico'],
        'E' => ['Procedimiento de soldadura (WPS)', 'Plan de calidad (ITP)', 'Procedimiento de izaje', 'Procedimiento de pruebas', 'Plan de seguridad y salud'],
        'F' => ['Certificados de materiales', 'Certificados de calibración', 'Certificados de competencia laboral', 'Pólizas de garantía', 'Cartas de cumplimiento'],
        'G' => ['Pruebas hidrostáticas', 'Pruebas no destructivas (NDT)', 'Pruebas de funcionamiento', 'Pruebas de integridad', 'Protocolos de comisionamiento'],
        'H' => ['Plan de seguridad', 'Análisis de riesgos (ATS)', 'Registro de capacitación', 'Reportes de inspección', 'Equipo de protección personal'],
        'I' => ['Reportes diarios de avance', 'Registro de mano de obra', 'Bitácora de frentes', 'Minutas de reunión', 'Estimaciones y facturación'],
        'J' => ['Garantías de equipos', 'Fianzas', 'Correspondencia oficial', 'Fotografías de obra', 'Cierre administrativo'],
    ];

    public function mount(Proyecto $id)
    {
        $this->proyecto = $id;
        $this->proyecto->load('cliente', 'sublinea');
        $this->loadSecciones();
    }

    public function loadSecciones()
    {
        $libro = LibroProyecto::where('proyecto_id', $this->proyecto->id)->first();

        if (!$libro) {
            $service = new AperturaLibroService();
            $service->abrir($this->proyecto);
            $libro = LibroProyecto::where('proyecto_id', $this->proyecto->id)->first();
        }

        $libro->load(['secciones.checklist', 'secciones.documentos.subidoPor']);

        $this->secciones = $libro->secciones->map(function ($seccion) {
            $checklist = $seccion->checklist->map(fn($item) => [
                'id' => $item->id,
                'descripcion' => $item->item_descripcion,
                'completado' => $item->completado,
            ])->toArray();

            if (empty($checklist)) {
                $plantilla = $this->checklistPlantillas[$seccion->codigo] ?? [];
                $checklist = collect($plantilla)->map(fn($desc) => [
                    'id' => null,
                    'descripcion' => $desc,
                    'completado' => false,
                ])->toArray();
            }

            $documentos = $seccion->documentos->map(fn($doc) => [
                'id' => $doc->id,
                'nombre' => $doc->nombre,
                'version' => $doc->version,
                'tamano' => $doc->tamaño ? $this->formatFileSize($doc->tamaño) : '—',
                'subidoPor' => $doc->subidoPor?->name ?? '—',
                'fecha' => $doc->subido_at ? $doc->subido_at->format('d/m/Y') : '—',
            ])->toArray();

            $totalItems = count($checklist);
            $completadosItems = collect($checklist)->filter(fn($i) => $i['completado'])->count();
            $porcentaje = $totalItems > 0 ? ($completadosItems / $totalItems) * 100 : 0;

            return [
                'id' => $seccion->id,
                'codigo' => $seccion->codigo,
                'nombre' => $seccion->nombre,
                'porcentaje' => round($porcentaje, 1),
                'completada' => $completadosItems === $totalItems && $totalItems > 0,
                'abierta' => false,
                'checklist' => $checklist,
                'documentos' => $documentos,
            ];
        })->toArray();

        $this->recalcularGlobal();
    }

    public function toggleSeccion($index)
    {
        $this->secciones[$index]['abierta'] = !$this->secciones[$index]['abierta'];
    }

    public function toggleChecklist($seccionIndex, $itemIndex)
    {
        $this->secciones[$seccionIndex]['checklist'][$itemIndex]['completado'] =
            !$this->secciones[$seccionIndex]['checklist'][$itemIndex]['completado'];

        $this->recalcularSeccion($seccionIndex);
        $this->recalcularGlobal();
    }

    public function recalcularSeccion($index)
    {
        $seccion = &$this->secciones[$index];
        $totalItems = count($seccion['checklist']);
        $completadosItems = collect($seccion['checklist'])->filter(fn($i) => $i['completado'])->count();
        $seccion['porcentaje'] = $totalItems > 0 ? round(($completadosItems / $totalItems) * 100, 1) : 0;
        $seccion['completada'] = $completadosItems === $totalItems && $totalItems > 0;
    }

    public function recalcularGlobal()
    {
        $this->itemsCompletados = 0;
        $this->itemsTotales = 0;

        foreach ($this->secciones as $seccion) {
            $this->itemsTotales += count($seccion['checklist']);
            foreach ($seccion['checklist'] as $item) {
                if ($item['completado']) {
                    $this->itemsCompletados++;
                }
            }
        }

        $this->porcentajeGlobal = $this->itemsTotales > 0
            ? round(($this->itemsCompletados / $this->itemsTotales) * 100, 1)
            : 0;
    }

    public function guardarAvance()
    {
        foreach ($this->secciones as $seccionData) {
            $seccion = LibroSeccion::find($seccionData['id']);
            if (!$seccion) {
                continue;
            }

            foreach ($seccionData['checklist'] as $itemData) {
                if ($itemData['id']) {
                    LibroSeccionChecklist::where('id', $itemData['id'])->update([
                        'completado' => $itemData['completado'],
                        'completado_por_id' => $itemData['completado'] ? auth()->id() : null,
                        'completado_at' => $itemData['completado'] ? now() : null,
                    ]);
                } else {
                    $seccion->checklist()->create([
                        'item_descripcion' => $itemData['descripcion'],
                        'completado' => $itemData['completado'],
                        'completado_por_id' => $itemData['completado'] ? auth()->id() : null,
                        'completado_at' => $itemData['completado'] ? now() : null,
                    ]);
                }
            }

            $totalItems = count($seccionData['checklist']);
            $completadosItems = collect($seccionData['checklist'])->filter(fn($i) => $i['completado'])->count();
            $seccion->update([
                'porcentaje_avance' => $totalItems > 0 ? (int) round(($completadosItems / $totalItems) * 100) : 0,
                'estado' => $completadosItems === $totalItems && $totalItems > 0 ? 'completo' : ($completadosItems > 0 ? 'en_proceso' : 'pendiente'),
            ]);
        }

        $libro = LibroProyecto::where('proyecto_id', $this->proyecto->id)->first();
        if ($libro) {
            $calculator = new AvanceCalculator();
            $libro->update([
                'porcentaje_avance_global' => $calculator->calcularAvanceGlobal($libro->id),
            ]);
        }

        $this->successMessage = 'Avance guardado correctamente.';
        $this->loadSecciones();
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function render()
    {
        return view('livewire.proyectos.libro-proyecto-editor')->layout('components.layouts.app');
    }
}