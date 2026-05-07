<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;

class Sublinea extends Model
{
    protected $table = 'sublineas';

    protected $fillable = ['codigo', 'nombre', 'descripcion'];
}
