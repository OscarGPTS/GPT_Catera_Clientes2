<?php

namespace App\Services\Libro;

use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;

class AperturaLibroService
{
    protected array $secciones = [
        'A' => ['nombre' => 'Cronograma de Actividades', 'descripcion' => 'Cronograma actualizado del proyecto'],
        'B' => ['nombre' => 'Ingeniería de Proyecto', 'descripcion' => 'Documentos de ingeniería, planos, memorias de cálculo'],
        'C' => ['nombre' => 'Permisos', 'descripcion' => 'Permisos de trabajo, licencias, autorizaciones'],
        'D' => ['nombre' => 'Estudios', 'descripcion' => 'Memorias de cálculo, plan de calidad, estudios técnicos'],
        'E' => ['nombre' => 'Procedimientos', 'descripcion' => 'Procedimientos operativos, instructivos de trabajo'],
        'F' => ['nombre' => 'Certificados', 'descripcion' => 'Certificados de personal, equipos, accesorios, materiales'],
        'G' => ['nombre' => 'Registro de Pruebas', 'descripcion' => 'Pruebas NDT, hidrostática, hermeticidad'],
        'H' => ['nombre' => 'Seguridad', 'descripcion' => 'IMSS, DC-3, AST, plan de emergencias'],
        'I' => ['nombre' => 'Ejecución', 'descripcion' => 'Bitácoras, reportes, registros de ejecución'],
        'J' => ['nombre' => 'Misceláneos', 'descripcion' => 'BOM/BOE, oficios, organigramas, correspondencia'],
    ];

    public function abrir(Proyecto $proyecto): void
    {
        DB::transaction(function () use ($proyecto) {
            $libro = DB::table('libros_proyecto')->insertGetId([
                'proyecto_id' => $proyecto->id,
                'fecha_apertura' => now(),
                'fecha_cierre_estimado' => $proyecto->fecha_fin_planeada,
                'porcentaje_avance_global' => 0,
                'bloqueado_para_cierre' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($this->secciones as $codigo => $data) {
                DB::table('libro_secciones')->insert([
                    'libro_id' => $libro,
                    'codigo' => $codigo,
                    'nombre' => $data['nombre'],
                    'descripcion' => $data['descripcion'],
                    'porcentaje_avance' => 0,
                    'estado' => 'pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
