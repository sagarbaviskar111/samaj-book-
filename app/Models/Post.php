<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = ['id', 'user_id', 'likes_count', 'comments_count'];

    public function user() { return $this->belongsTo(User::class); }
    public function likes() { return $this->hasMany(Like::class); }
    public function comments() { return $this->hasMany(Comment::class)->latest(); }
}
