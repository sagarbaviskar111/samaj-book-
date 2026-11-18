<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\Interest;
use App\Models\User;
use App\Support\BiodataOptions;
use Illuminate\Database\Seeder;

/**
 * Demo kundli values + one accepted interest (Rahul 9000000001 <-> Pooja 9000000021).
 * Idempotent. Run: php artisan db:seed --class=DemoKundliSeeder
 */
class DemoKundliSeeder extends Seeder
{
    public function run(): void
    {
        $naks = array_keys(BiodataOptions::nakshatras());
        $fixed = [
            '9000000001' => ['rohini', 2],   // -> vrishabha
            '9000000021' => ['hasta', 3],    // -> kanya
        ];

        foreach (Biodata::whereNull('nakshatra')->orWhereNull('rashi')->orderBy('id')->get() as $b) {
            [$n, $p] = $fixed[$b->user?->phone] ?? [$naks[($b->id * 7) % 27], (($b->id * 3) % 4) + 1];
            $b->forceFill(['nakshatra' => $n, 'nakshatra_pada' => $p, 'rashi' => BiodataOptions::rashiFromNakshatraPada($n, $p)])->save();
        }

        $rahul = Biodata::whereHas('user', fn ($q) => $q->where('phone', '9000000001'))->where('added_by', 'self')->first();
        $pooja = Biodata::whereHas('user', fn ($q) => $q->where('phone', '9000000021'))->first();
        if (! $rahul || ! $pooja) {
            return;
        }
        foreach ([[$rahul, 'rohini', 2], [$pooja, 'hasta', 3]] as [$b, $n, $p]) {
            $b->forceFill(['nakshatra' => $n, 'nakshatra_pada' => $p, 'rashi' => BiodataOptions::rashiFromNakshatraPada($n, $p)])->save();
        }

        $i = Interest::where(fn ($q) => $q->where('from_biodata_id', $rahul->id)->where('to_biodata_id', $pooja->id))
            ->orWhere(fn ($q) => $q->where('from_biodata_id', $pooja->id)->where('to_biodata_id', $rahul->id))->first()
            ?? new Interest(['from_biodata_id' => $rahul->id, 'to_biodata_id' => $pooja->id]);
        $i->forceFill([
            'from_user_id' => $i->fromBiodata?->user_id ?? $rahul->user_id,
            'to_user_id' => $i->toBiodata?->user_id ?? $pooja->user_id,
            'status' => 'accepted', 'responded_at' => $i->responded_at ?? now(), 'expires_at' => null,
        ])->save();
    }
}
