<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAllowlist extends Model
{
    protected $table = 'email_allowlist';

    protected $fillable = [
        'email',
        'allowed_providers',
        'role_default',
        'departamento_default',
    ];

    protected function casts(): array
    {
        return [
            'allowed_providers' => 'array',
        ];
    }

    public function allowsProvider(string $provider): bool
    {
        if (empty($this->allowed_providers)) {
            return true;
        }

        return in_array($provider, $this->allowed_providers);
    }
}