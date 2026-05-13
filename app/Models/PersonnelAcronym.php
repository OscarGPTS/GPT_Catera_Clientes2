<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelAcronym extends Model
{
    protected $table = 'personnel_acronyms';

    protected $fillable = [
        'user_id',
        'acronym',
        'account_manager',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
