<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Biodata extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'status', 'verified', 'approved_at', 'approved_by', 'views', 'user_id', 'share_token', 'share_enabled'];

    protected $casts = [
        'dob' => 'date',
        'manglik' => 'boolean',
        'verified' => 'boolean',
        'income_private' => 'boolean',
        'requests_to_parent' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function photos() { return $this->hasMany(BiodataPhoto::class)->orderBy('sort'); }
    public function primaryPhoto() { return $this->hasOne(BiodataPhoto::class)->where('is_primary', true); }
    public function sentInterests() { return $this->hasMany(Interest::class, 'from_biodata_id'); }
    public function receivedInterests() { return $this->hasMany(Interest::class, 'to_biodata_id'); }

    public function getAgeAttribute(): int { return $this->dob->age; }

    /** Lazily create the share token (url-safe, unique). */
    public function ensureShareToken(bool $regenerate = false): string
    {
        if ($regenerate || ! $this->share_token) {
            do {
                $token = \Illuminate\Support\Str::random(20);
            } while (static::where('share_token', $token)->exists());
            $this->forceFill(['share_token' => $token])->save();
        }

        return $this->share_token;
    }

    public function shareUrl(): ?string
    {
        return $this->share_token ? route('biodata.shared', $this->share_token) : null;
    }

    public function scopeApproved(Builder $q): Builder { return $q->where('status', 'approved'); }

    /** Fields completed / total, used for the "profile completeness" bar. */
    public function completeness(): int
    {
        $fields = ['name','gender','dob','height_cm','education','occupation','father_name','mother_name','gotra','district','current_city','about','expectation','contact_phone'];
        $filled = collect($fields)->filter(fn ($f) => filled($this->$f))->count();
        $filled += $this->photos()->exists() ? 1 : 0;
        return (int) round($filled / (count($fields) + 1) * 100);
    }
}
