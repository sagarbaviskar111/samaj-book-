<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /** Every superadmin-area action is recorded with this action prefix (e.g. sa.payment.create). */
    public const SA_PREFIX = 'sa.';

    protected $guarded = ['id'];

    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }

    public static function record(string $action, ?string $type = null, ?int $id = null, ?string $note = null): void
    {
        static::create(['admin_id' => auth()->id(), 'action' => $action, 'target_type' => $type, 'target_id' => $id, 'note' => $note]);
    }

    /** Superadmin-area action: prefixed with "sa." so regular admins never see it. */
    public static function recordSa(string $action, ?string $type = null, ?int $id = null, ?string $note = null): void
    {
        static::record(self::SA_PREFIX.$action, $type, $id, $note);
    }

    /** What regular admins may see: nothing done by a superadmin and no sa.* actions. */
    public function scopeVisibleToAdmins(Builder $q): Builder
    {
        return $q->where('action', 'not like', self::SA_PREFIX.'%')
            ->where(fn ($w) => $w->whereNull('admin_id')->orWhereNotIn('admin_id', User::query()->where('role', 'superadmin')->select('id')));
    }
}
