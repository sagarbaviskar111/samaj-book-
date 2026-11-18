<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shortlist extends Model
{
    protected $guarded = ['id'];

    public function biodata() { return $this->belongsTo(Biodata::class); }
    public function user() { return $this->belongsTo(User::class); }
}
