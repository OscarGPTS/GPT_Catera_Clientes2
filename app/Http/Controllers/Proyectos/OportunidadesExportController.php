<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OportunidadesExportController extends Controller
{
    // Mapa de estados del enum al texto legible
    private const ESTADOS = [
        'en_revision'          => 'En revisión',
        'cotizando'            => 'Cotizando',
        'cotizado'             => 'Cotizado',
        'presentado'           => 'Presentado',
        'adjudicado_pendiente' => 'Adjudicado (pend.)',
        'adjudicado_firmado'   => 'Adjudicado',
        'en_ejecucion'         => 'En ejecución',
        'en_cierre'            => 'En cierre',
        'cerrado'              => 'Cerrado',
        'cancelado'            => 'Cancelado',
        'perdido'              => 'Perdido',
        'archivado'            => 'Archivado',
    ];

    public function __invoke(Request $request)
    {
        // ── Construir query con los mismos filtros del índice ──────────────────
        $query = Proyecto::with(['cliente', 'lugar', 'elaboro'])
            ->orderByRaw('fecha_envio IS NULL, fecha_envio DESC');

        if ($request->filled('anio') && $request->anio !== 'todos') {
            $query->where('anio', (int) $request->anio);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(cp_numero) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(dn_numero) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('cliente', fn($c) => $c->whereRaw('LOWER(razon_social) LIKE ?', ["%{$search}%"]));
            });
        }

        if ($request->filled('estados')) {
            $query->whereIn('estado', $request->estados);
        }

        $proyectos = $query->get();

        // ── Spreadsheet ────────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ofertas');

        // ── Filas 1-5: Cabecera del reporte ────────────────────────────────────
        // Fila 1: Título principal (A1:R1)
        $sheet->mergeCells('A1:R1');
        $sheet->setCellValue('A1', 'GPT SERVICES — STATUS DE OFERTAS');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Fila 2: Subtítulo / año filtrado
        $anioLabel = ($request->filled('anio') && $request->anio !== 'todos')
            ? 'Año ' . $request->anio
            : 'Todos los años';
        $sheet->mergeCells('A2:R2');
        $sheet->setCellValue('A2', $anioLabel . '   |   Generado: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2D5F8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Filas 3-5: espacio en blanco (pueden usarse para logos / notas)
        foreach ([3, 4, 5] as $r) {
            $sheet->mergeCells("A{$r}:R{$r}");
            $sheet->getRowDimension($r)->setRowHeight(6);
        }

        // ── Fila 6: Encabezados de columna ─────────────────────────────────────
        $headers = [
            'A' => 'CP',
            'B' => 'CLIENTE',
            'C' => 'CONTACTO',
            'D' => 'DATOS DE CONTACTO',
            'E' => 'LUGAR',
            'F' => 'ALCANCE',
            'G' => 'OFERTA',
            'H' => 'FECHA ENVÍO',
            'I' => 'FECHA MOD. OFERTA',
            'J' => 'OFERTAS EMITIDAS',
            'K' => 'HITOS DE PAGO',
            'L' => 'RESPONSABLE',
            'M' => 'STATUS',
            'N' => 'OFERTA (PDF)',
            'O' => 'CONCEPTO DE ADJUDICACIÓN',
            'P' => '% DE ADJUDICACION',
            'Q' => '%',
            'R' => 'CARTERA ESPERADA',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}6", $label);
        }

        $sheet->getStyle('A6:R6')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']],
            ],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // ── Filas 7+: Datos ────────────────────────────────────────────────────
        $row = 7;
        foreach ($proyectos as $p) {
            $sheet->setCellValue("A{$row}", $p->cp_numero);
            $sheet->setCellValue("B{$row}", $p->cliente?->alias_3letras ?? $p->cliente?->razon_social);
            $sheet->setCellValue("C{$row}", $p->contacto);
            $sheet->setCellValue("D{$row}", $p->datos_contacto);
            $sheet->setCellValue("E{$row}", $p->lugar?->nombre);
            $sheet->setCellValue("F{$row}", $p->alcance);
            $sheet->setCellValue("G{$row}", $p->tech_reference);

            // Fechas como cadenas DD/MM/YYYY
            $sheet->setCellValue("H{$row}", $p->fecha_envio?->format('d/m/Y'));
            $sheet->setCellValue("I{$row}", $p->fecha_modificacion_oferta?->format('d/m/Y'));

            // Monto numérico
            if ($p->monto_usd !== null) {
                $sheet->setCellValueExplicit("J{$row}", (float) $p->monto_usd, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
            }

            $sheet->setCellValue("K{$row}", $p->hitos_pago);
            $sheet->setCellValue("L{$row}", $p->elaboro?->name);
            $sheet->setCellValue("M{$row}", self::ESTADOS[$p->estado] ?? $p->estado);
            $sheet->setCellValue("N{$row}", $p->archivo_oferta);
            $sheet->setCellValue("O{$row}", $p->concepto_adjudicacion);

            // Ponderación como número
            $sheet->setCellValueExplicit("P{$row}", (int) $p->ponderacion, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle("P{$row}")->getNumberFormat()->setFormatCode('0"%"');

            // % adjudicación efectivo
            if ($p->porcentaje_adjudicacion !== null) {
                $sheet->setCellValueExplicit("Q{$row}", (float) $p->porcentaje_adjudicacion, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode('0.00"%"');
            }

            // Cartera esperada
            if ($p->cartera_esperada !== null) {
                $sheet->setCellValueExplicit("R{$row}", (float) $p->cartera_esperada, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->getStyle("R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
            }

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:R{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF0F4FA');
            }

            // Bordes ligeros
            $sheet->getStyle("A{$row}:R{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFDDDDDD']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => false],
            ]);

            $sheet->getRowDimension($row)->setRowHeight(16);
            $row++;
        }

        // ── Anchos de columna ──────────────────────────────────────────────────
        $widths = [
            'A' => 12,  // CP
            'B' => 10,  // CLIENTE
            'C' => 20,  // CONTACTO
            'D' => 28,  // DATOS CONTACTO
            'E' => 18,  // LUGAR
            'F' => 35,  // ALCANCE
            'G' => 35,  // OFERTA (código)
            'H' => 14,  // FECHA ENVÍO
            'I' => 14,  // FECHA MOD
            'J' => 16,  // MONTO
            'K' => 25,  // HITOS
            'L' => 18,  // RESPONSABLE
            'M' => 16,  // STATUS
            'N' => 25,  // OFERTA PDF
            'O' => 30,  // CONCEPTO ADJ
            'P' => 10,  // % ADJ
            'Q' => 10,  // %
            'R' => 16,  // CARTERA
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Freeze rows 1-6 (encabezado fijo al scroll)
        $sheet->freezePane('A7');

        // ── Generar respuesta ──────────────────────────────────────────────────
        $filename = 'ofertas-' . now()->format('Ymd-Hi') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
