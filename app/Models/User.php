<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'phone', 'email', 'password', 'gotra', 'state', 'district', 'city', 'area', 'avatar', 'language'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function followers() { return $this->belongsToMany(self::class, 'follows', 'following_id', 'follower_id')->withTimestamps(); }
    public function followings() { return $this->belongsToMany(self::class, 'follows', 'follower_id', 'following_id')->withTimestamps(); }
    /** Ids of members this user follows (loaded once per request). */
    public function followingIds(): array
    {
        return $this->followingIdsCache ??= \Illuminate\Support\Facades\DB::table('follows')
            ->where('follower_id', $this->id)->pluck('following_id')->flip()->all();
    }

    public function isFollowing(int $userId): bool
    {
        return isset($this->followingIds()[$userId]);
    }

    private ?array $followingIdsCache = null;

    public function isAdmin(): bool { return in_array($this->role, ['superadmin', 'admin', 'subadmin'], true); }
    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    /** admin or superadmin: everything except being area-limited. */
    public function isFullAdmin(): bool { return in_array($this->role, ['superadmin', 'admin'], true); }
    public function roleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => __('Super admin'),
            'admin' => __('Admin'),
            'subadmin' => __('Sub-admin').' · '.($this->admin_area ?: '—'),
            default => __('Member'),
        };
    }
    public function isSubAdmin(): bool { return $this->role === 'subadmin'; }
    public function isActive(): bool { return $this->status === 'active'; }
    /** Registered member waiting for admin approval: read-only feed access only. */
    public function isPending(): bool { return $this->role === 'user' && $this->status === 'pending'; }

    public function biodatas() { return $this->hasMany(Biodata::class); }
    public function posts() { return $this->hasMany(Post::class); }
    public function shortlists() { return $this->hasMany(Shortlist::class); }
    public function lookingFor() { return $this->hasOne(LookingForProfile::class); }
}
