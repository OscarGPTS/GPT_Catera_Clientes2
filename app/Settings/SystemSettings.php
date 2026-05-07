<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{
    public bool $minuta_entrega_obligatoria;
    public bool $bloqueo_cierre_dossier_incompleto;
    public bool $bloqueo_cierre_post_mortem_pendiente;
    public array $auth_dominios_corporativos;
    public int $concentracion_cliente_alerta_umbral;

    public static function group(): string
    {
        return 'system';
    }
}
