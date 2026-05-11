<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;

class Secuencia extends Model
{
    protected $table = 'secuencias';

    protected $fillable = ['tipo', 'anio', 'ultimo_consecutivo'];
}
