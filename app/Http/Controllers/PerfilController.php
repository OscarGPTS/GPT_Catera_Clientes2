<?php

namespace App\Http\Controllers;

use App\Services\Auth\SocioResolver;
use App\Settings\SystemSettings;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles', 'authProviders');

        return view('perfil.index', compact('user'));
    }

    public function miAsignacion()
    {
        return view('perfil.asignacion');
    }
}