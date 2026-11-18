<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public const INTERVALS = [5, 10, 15, 20, 30, 60];
    /** Hours an event without ends_at is considered running. */
    public const DEFAULT_DURATION_H = 3;

    protected $guarded = ['id'];
    protected $casts = [
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
        'is_published' => 'boolean', 'registration_open' => 'boolean', 'popup_enabled' => 'boolean',
        'reminder_interval_min' => 'integer',
    ];

    public function registrations() { return $this->hasMany(EventRegistration::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePublished($q) { return $q->where('is_published', true); }

    /** Not finished yet (running or in the future). */
    public function scopeUpcoming($q)
    {
        return $q->where(function ($w) {
            $w->where('ends_at', '>=', now())
                ->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '>=', now()->subHours(self::DEFAULT_DURATION_H)));
        });
    }

    public function scopePast($q)
    {
        return $q->where(function ($w) {
            $w->where('ends_at', '<', now())
                ->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '<', now()->subHours(self::DEFAULT_DURATION_H)));
        });
    }

    /** Starts today (app timezone). */
    public function scopeToday($q)
    {
        return $q->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    /** Events for the member's area, or for everybody (area null). */
    public function scopeForAudience($q, User $user)
    {
        return $q->where(function ($w) use ($user) {
            $w->whereNull('area');
            $areas = array_values(array_filter([$user->district, $user->city]));
            if ($areas) {
                $w->orWhereIn('area', $areas);
            }
        });
    }

    public function endsAtEffective()
    {
        return $this->ends_at ?? $this->starts_at->copy()->addHours(self::DEFAULT_DURATION_H);
    }

    public function isOver(): bool { return $this->endsAtEffective()->isPast(); }
    public function isToday(): bool { return $this->starts_at->isToday(); }

    public function acceptsRegistration(): bool
    {
        return $this->is_published && $this->registration_open && ! $this->isOver();
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function startsHuman(): string
    {
        return $this->starts_at->format('D, d M Y').' · '.$this->starts_at->format('h:i A');
    }
}
