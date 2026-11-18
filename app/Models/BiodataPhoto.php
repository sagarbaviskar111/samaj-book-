<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiodataPhoto extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $guarded = ['id'];
    protected $casts = ['is_primary' => 'boolean'];

    public function biodata() { return $this->belongsTo(Biodata::class); }
}
