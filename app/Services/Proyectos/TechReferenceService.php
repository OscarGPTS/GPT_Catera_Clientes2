<?php

namespace App\Services\Proyectos;

class TechReferenceService
{
    /**
     * Regex validation: ^\d{6}-\d{1,2}-[A-Z]{3}-[A-Z&]{3,4}\s+(\d+x\d+|x)\s+_.{1,80}$
     */
    public function validar(string $techReference): bool
    {
        return (bool) preg_match(
            '/^\d{6}-\d{1,2}-[A-Z]{3}-[A-Z&]{3,4}\s+(\d+x\d+|x)\s+_.{1,80}$/',
            $techReference
        );
    }

    /**
     * Generates a tech reference from components.
     * Example: 250121-0-IGA-HTP x _HT 30"x 10" Texmelucan
     */
    public function generar(
        string $fecha,
        string $consecutivo,
        string $aliasCliente,
        string $sublinea,
        string $dimensiones,
        string $descripcion
    ): string {
        $fechaComp = date('ymd', strtotime($fecha));
        $dim = $dimensiones ?: 'x';

        return sprintf(
            '%s-%s-%s-%s %s _%s',
            $fechaComp,
            $consecutivo,
            strtoupper($aliasCliente),
            strtoupper($sublinea),
            $dim,
            $descripcion
        );
    }
}
