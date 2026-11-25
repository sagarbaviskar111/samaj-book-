<?php

namespace App\Services;

use App\Models\Biodata;
use App\Models\Interest;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Support\BiodataOptions;
use DomainException;

class InterestService
{
    public const DAILY_LIMIT = 10;
    public const EXPIRY_DAYS = 7;

    public static function dailyLimit(): int
    {
        return max(1, (int) Setting::get('interest_daily_limit', self::DAILY_LIMIT));
    }

    public function sentToday(User $user): int
    {
        return Interest::where('from_user_id', $user->id)->where('created_at', '>=', now()->startOfDay())->count();
    }

    /** @throws DomainException with a user-facing message */
    public function send(User $user, int $fromBiodataId, int $toBiodataId, ?string $message = null): Interest
    {
        $from = Biodata::approved()->where('user_id', $user->id)->find($fromBiodataId);
        if (! $from) {
            throw new DomainException(
                Biodata::approved()->where('user_id', $user->id)->exists()
                    ? __('पहले अपना मंज़ूर बायोडाटा चुनिए।')
                    : __('पसंद भेजने के लिए पहले अपना बायोडाटा बनाइए और उसके मंज़ूर होने का इंतज़ार कीजिए।')
            );
        }
        $to = Biodata::approved()->find($toBiodataId);
        if (! $to) {
            throw new DomainException(__('यह बायोडाटा अभी उपलब्ध नहीं है।'));
        }
        if ($to->user_id === $user->id) {
            throw new DomainException(__('आप अपने ही बायोडाटा को पसंद नहीं भेज सकते।'));
        }
        if ($to->gender === $from->gender) {
            throw new DomainException(__('यह बायोडाटा आपके लिए उपलब्ध नहीं है।'));
        }

        $reverse = Interest::where('from_biodata_id', $to->id)->where('to_biodata_id', $from->id)
            ->whereIn('status', ['sent', 'accepted'])->exists();
        if ($reverse) {
            throw new DomainException(__('इन्होंने पहले ही आपको पसंद भेजी है। "रिश्ते की पसंद" पेज देखिए।'));
        }

        $existing = Interest::where('from_biodata_id', $from->id)->where('to_biodata_id', $to->id)->first();
        if ($existing && ! in_array($existing->status, ['expired', 'withdrawn'], true)) {
            throw new DomainException(__('आप इन्हें पहले ही पसंद भेज चुके हैं।'));
        }

        if ($this->sentToday($user) >= self::dailyLimit()) {
            throw new DomainException(__('आज की सीमा (:n पसंद) पूरी हो गई। कल फिर कोशिश कीजिए।', ['n' => self::dailyLimit()]));
        }

        $data = [
            'from_user_id' => $user->id,
            'to_user_id' => $to->user_id,
            'status' => 'sent',
            'message' => $message ? mb_substr($message, 0, 300) : null,
            'responded_at' => null,
            'expires_at' => now()->addDays(self::EXPIRY_DAYS),
        ];
        if ($existing) {
            $existing->forceFill($data + ['created_at' => now()])->save();
            $interest = $existing;
        } else {
            $interest = Interest::create($data + ['from_biodata_id' => $from->id, 'to_biodata_id' => $to->id]);
        }

        // The biodata owner is the receiver (parents who made the biodata get it directly).
        $relation = $from->added_by !== 'self' ? BiodataOptions::profileRelation($from->added_by, $from->gender) : '';
        if ($relation !== '') {
            $to->user?->notify(new GeneralNotification(
                'interest_received',
                __(':sender ने अपने :rel :name के लिए पसंद भेजी है', ['sender' => $user->name, 'rel' => $relation, 'name' => $this->firstName($from)]),
                __(':sender ने अपने :rel :name की तरफ़ से आपके बायोडाटा (:to) के लिए पसंद भेजी है।', ['sender' => $user->name, 'rel' => $relation, 'name' => $this->firstName($from), 'to' => $this->firstName($to)]),
                route('interests.index', ['tab' => 'received']),
            ));
        } else {
            $to->user?->notify(new GeneralNotification(
                'interest_received',
                __(':name की तरफ़ से रिश्ते की पसंद', ['name' => $this->firstName($from)]),
                __(':name जी ने आपके बायोडाटा (:to) के लिए पसंद भेजी है।', ['name' => $this->firstName($from), 'to' => $this->firstName($to)]),
                route('interests.index', ['tab' => 'received']),
            ));
        }

        return $interest;
    }

    /**
     * The sender's approved biodatas that may be paired with $target (opposite gender).
     *
     * @return \Illuminate\Support\Collection<int,Biodata>
     */
    public static function eligibleSenders(User $user, Biodata $target)
    {
        return Biodata::approved()->where('user_id', $user->id)->where('gender', '!=', $target->gender)
            ->orderBy('id')->get(['id', 'name', 'gender', 'added_by']);
    }

    /** "Apne liye: Rahul" or "Neha Gaikwad (cousin)". */
    public static function senderLabel(Biodata $b): string
    {
        if ($b->added_by === 'self') {
            return __('अपने लिए: :name', ['name' => $b->name]);
        }

        return $b->name.' ('.BiodataOptions::profileRelation($b->added_by, $b->gender).')';
    }

    public function respond(User $user, Interest $interest, string $action): Interest
    {
        if ($interest->to_user_id !== $user->id) {
            throw new DomainException(__('आप इस पर जवाब नहीं दे सकते।'));
        }
        if ($interest->status !== 'sent') {
            throw new DomainException(__('इस पर पहले ही जवाब दिया जा चुका है।'));
        }
        if ($interest->expires_at && $interest->expires_at->isPast()) {
            throw new DomainException(__('यह पसंद का समय पूरा हो चुका है।'));
        }

        $accept = $action === 'accept';
        $interest->update(['status' => $accept ? 'accepted' : 'rejected', 'responded_at' => now()]);
        $interest->loadMissing('toBiodata', 'fromBiodata');
        $name = $this->firstName($interest->toBiodata);
        $rel = $interest->relationLabel();
        $relName = $rel !== '' ? __('आपके :rel :name', ['rel' => $rel, 'name' => $this->firstName($interest->fromBiodata)]) : '';

        $interest->fromUser?->notify(new GeneralNotification(
            $accept ? 'interest_accepted' : 'interest_rejected',
            $accept
                ? ($rel !== '' ? __(':name ने :rel की पसंद मंज़ूर की!', ['name' => $name, 'rel' => $relName]) : __(':name ने आपकी पसंद मंज़ूर की!', ['name' => $name]))
                : ($rel !== '' ? __(':rel की पसंद पर जवाब आया', ['rel' => ucfirst($relName)]) : __('आपकी पसंद पर जवाब आया')),
            $accept
                ? __('अब आप दोनों एक-दूसरे का संपर्क नंबर देख सकते हैं।')
                : __('धन्यवाद, इस बार संयोग नहीं बैठा। आपको जल्द ही सही रिश्ता मिले, शुभकामनाएँ।'),
            route('interests.index', ['tab' => $accept ? 'accepted' : 'sent']),
        ));

        return $interest;
    }

    public function withdraw(User $user, Interest $interest): Interest
    {
        if ($interest->from_user_id !== $user->id || $interest->status !== 'sent') {
            throw new DomainException(__('यह पसंद अब वापस नहीं ली जा सकती।'));
        }
        $interest->update(['status' => 'withdrawn']);

        return $interest;
    }

    public function requestHelp(User $user, Interest $interest): Report
    {
        if ($interest->status !== 'accepted' || ! in_array($user->id, [$interest->from_user_id, $interest->to_user_id], true)) {
            throw new DomainException(__('मदद सिर्फ़ मंज़ूर हुए रिश्ते पर मिल सकती है।'));
        }

        return Report::firstOrCreate(
            ['reporter_id' => $user->id, 'target_type' => 'interest_help', 'target_id' => $interest->id, 'status' => 'open'],
            ['reason' => 'Admin se madad: mil-baithne ka intezaam']
        );
    }

    public function expireDue(): int
    {
        return Interest::where('status', 'sent')->whereNotNull('expires_at')->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    private function firstName(?Biodata $b): string
    {
        return $b ? (explode(' ', trim($b->name))[0] ?? $b->name) : '';
    }
}
