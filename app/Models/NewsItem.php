<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['event_at' => 'datetime', 'is_published' => 'boolean', 'show_on_landing' => 'boolean'];

    public function scopePublished($q) { return $q->where('is_published', true); }
}
