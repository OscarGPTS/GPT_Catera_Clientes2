<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreBusiness extends Model
{
    protected $table = 'core_businesses';

    protected $fillable = [
        'core_business',
        'acronym',
        'description',
    ];
}
