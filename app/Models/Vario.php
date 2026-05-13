<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vario extends Model
{
    protected $table = 'varios';

    protected $fillable = [
        'nombre',
    ];
}
