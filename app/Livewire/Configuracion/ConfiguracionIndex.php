<?php

namespace App\Livewire\Configuracion;

use App\Settings\SystemSettings;
use Livewire\Component;

class ConfiguracionIndex extends Component
{
    public $minuta_entrega_obligatoria;
    public $bloqueo_cierre_dossier_incompleto;
    public $bloqueo_cierre_post_mortem_pendiente;
    public $concentracion_cliente_alerta_umbral;
    public $auth_dominios_corporativos_input;

    public $saved = false;

    public function mount()
    {
        $settings = app(SystemSettings::class);
        $this->minuta_entrega_obligatoria = $settings->minuta_entrega_obligatoria;
        $this->bloqueo_cierre_dossier_incompleto = $settings->bloqueo_cierre_dossier_incompleto;
        $this->bloqueo_cierre_post_mortem_pendiente = $settings->bloqueo_cierre_post_mortem_pendiente;
        $this->concentracion_cliente_alerta_umbral = $settings->concentracion_cliente_alerta_umbral;
        $this->auth_dominios_corporativos_input = implode(', ', $settings->auth_dominios_corporativos);
    }

    protected function rules()
    {
        return [
            'minuta_entrega_obligatoria' => 'boolean',
            'bloqueo_cierre_dossier_incompleto' => 'boolean',
            'bloqueo_cierre_post_mortem_pendiente' => 'boolean',
            'concentracion_cliente_alerta_umbral' => 'required|integer|min:1|max:100',
            'auth_dominios_corporativos_input' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $settings = app(SystemSettings::class);
        $settings->minuta_entrega_obligatoria = $this->minuta_entrega_obligatoria;
        $settings->bloqueo_cierre_dossier_incompleto = $this->bloqueo_cierre_dossier_incompleto;
        $settings->bloqueo_cierre_post_mortem_pendiente = $this->bloqueo_cierre_post_mortem_pendiente;
        $settings->concentracion_cliente_alerta_umbral = $this->concentracion_cliente_alerta_umbral;
        $settings->auth_dominios_corporativos = array_filter(array_map('trim', explode(',', $this->auth_dominios_corporativos_input)));
        $settings->save();

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.configuracion.configuracion-index')->layout('components.layouts.app');
    }
}