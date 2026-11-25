<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookingForProfile extends Model
{
    protected $guarded = ['id'];

    public const FOR_WHOM = [
        'son' => 'बेटा',
        'daughter' => 'बेटी',
        'brother' => 'भाई',
        'sister' => 'बहन',
        'cousin' => 'कज़िन (चचेरे/ममेरे भाई-बहन)',
        'relative' => 'रिश्तेदार',
        'other' => 'कोई और',
    ];

    public function user() { return $this->belongsTo(User::class); }

    /** Label without the Hindi part, for sentences. */
    public function forWhomLabel(): string
    {
        return __(trim(explode(' (', self::FOR_WHOM[$this->for_whom] ?? $this->for_whom)[0]));
    }
}
