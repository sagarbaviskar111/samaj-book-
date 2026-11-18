<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Raw rows: `value` holds a Crypt::encryptString payload. Use App\Services\IntegrationSettings instead of this model. */
class IntegrationSecret extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['value'];

    protected function casts(): array
    {
        return ['is_secret' => 'boolean'];
    }
}
