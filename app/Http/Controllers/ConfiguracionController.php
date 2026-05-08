<?php

namespace App\Http\Controllers;

use App\Settings\SystemSettings;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $settings = app(SystemSettings::class);

        return view('configuracion', compact('settings'));
    }
}