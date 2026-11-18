<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['responded_at' => 'datetime', 'expires_at' => 'datetime'];

    public function fromBiodata() { return $this->belongsTo(Biodata::class, 'from_biodata_id'); }
    public function toBiodata() { return $this->belongsTo(Biodata::class, 'to_biodata_id'); }
    public function fromUser() { return $this->belongsTo(User::class, 'from_user_id'); }
    public function toUser() { return $this->belongsTo(User::class, 'to_user_id'); }

    /** Sent by a member for a relative (the sending biodata was not added by the profile person himself/herself). */
    public function isOnBehalf(): bool
    {
        return $this->fromBiodata && $this->fromBiodata->added_by !== 'self';
    }

    /** Romanized relation of the sending profile to the sender, e.g. "cousin" (empty when not on behalf). */
    public function relationLabel(): string
    {
        return $this->isOnBehalf() ? \App\Support\BiodataOptions::profileRelation($this->fromBiodata->added_by, $this->fromBiodata->gender) : '';
    }
}
