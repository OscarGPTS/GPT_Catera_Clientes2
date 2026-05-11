<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RolesPermisosController extends Controller
{
    public function index()
    {
        return view('admin.roles-permisos');
    }
}