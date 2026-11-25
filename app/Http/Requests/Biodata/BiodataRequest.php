<?php

namespace App\Http\Requests\Biodata;

use App\Support\BiodataOptions as O;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Shared by create and update. "save_draft" relaxes the contact requirement. */
class BiodataRequest extends FormRequest
{
    public const MIN_AGE = ['female' => 18, 'male' => 21];

    public function authorize(): bool
    {
        $biodata = $this->route('biodata');

        // update: owner only (runs before validation); create: any logged-in member
        return $this->user() !== null
            && (! $biodata instanceof \App\Models\Biodata || \App\Support\BiodataAccess::canManage($this->user(), $biodata));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'income_private' => $this->boolean('income_private'),
            'requests_to_parent' => $this->boolean('requests_to_parent'),
            'contact_phone' => $this->filled('contact_phone') ? $this->normalizePhone((string) $this->input('contact_phone')) : null,
            'manglik' => match ((string) $this->input('manglik')) { '1' => true, '0' => false, default => null },
        ]);
    }

    private function normalizePhone(string $p): string
    {
        $d = preg_replace('/[^0-9]/', '', $p);

        return strlen($d) > 10 ? substr($d, -10) : $d; // drop +91 / 0 prefix
    }

    public function isDraft(): bool
    {
        return $this->boolean('save_draft');
    }

    public function rules(): array
    {
        $in = fn (array $list) => Rule::in(array_keys($list));
        $str = ['nullable', 'string', 'max:150'];

        return [
            'added_by' => ['required', $in(O::addedBy())],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', $in(O::genders())],
            'dob' => ['required', 'date', 'before:today', 'after:1940-01-01'],
            'height_cm' => ['nullable', 'integer', 'between:120,230'],
            'weight_kg' => ['nullable', 'integer', 'between:30,200'],
            'complexion' => ['nullable', $in(O::complexions())],
            'blood_group' => ['nullable', $in(O::bloodGroups())],
            'marital_status' => ['required', $in(O::maritalStatuses())],
            'manglik' => ['nullable', 'boolean'],
            'disability' => $str,
            'birth_time' => ['nullable', 'date_format:H:i'],
            'birth_place' => $str,
            'rashi' => ['nullable', $in(O::rashis())],
            'nakshatra' => ['nullable', $in(O::nakshatras())],
            'nakshatra_pada' => ['nullable', 'integer', 'between:1,4'],

            'education' => $str,
            'education_level' => ['nullable', $in(O::educationLevels())],
            'occupation_type' => ['nullable', $in(O::occupationTypes())],
            'occupation' => $str,
            'company' => $str,
            'income_range' => ['nullable', $in(O::incomeRanges())],
            'income_private' => ['boolean'],

            'father_name' => $str,
            'father_occupation' => $str,
            'mother_name' => $str,
            'mother_occupation' => $str,
            'brothers' => ['nullable', 'integer', 'between:0,15'],
            'brothers_married' => ['nullable', 'integer', 'between:0,15', 'lte:brothers'],
            'sisters' => ['nullable', 'integer', 'between:0,15'],
            'sisters_married' => ['nullable', 'integer', 'between:0,15', 'lte:sisters'],
            'family_type' => ['nullable', $in(O::familyTypes())],
            'mama_gotra' => $str,
            'mama_village' => $str,

            'gotra' => $str,
            'upjati' => $str,
            'village' => $str,
            'tehsil' => $str,
            'district' => $str,
            'state' => $str,
            'current_city' => $str,
            'area' => $str,

            'about' => ['nullable', 'string', 'max:2000'],
            'expectation' => ['nullable', 'string', 'max:2000'],
            'pref_age_min' => ['nullable', 'integer', 'between:18,70'],
            'pref_age_max' => ['nullable', 'integer', 'between:18,70', 'gte:pref_age_min'],
            'pref_education_level' => ['nullable', $in(O::educationLevels())],
            'pref_district' => $str,

            'contact_phone' => [$this->isDraft() ? 'nullable' : 'required', 'regex:/^[6-9][0-9]{9}$/'],
            'photo_privacy' => ['required', $in(O::photoPrivacies())],
            'requests_to_parent' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_phone.regex' => __('10 अंकों का सही मोबाइल नंबर डालिए (जैसे 9876543210)।'),
            'contact_phone.required' => __('बायोडाटा भेजने के लिए संपर्क नंबर ज़रूरी है। (ड्राफ़्ट सेव करने के लिए ज़रूरी नहीं।)'),
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            $this->validateKundli($v);
            if ($v->errors()->has('dob') || $v->errors()->has('gender') || ! $this->filled('dob')) {
                return;
            }
            $min = self::MIN_AGE[$this->input('gender')] ?? 21;
            if (Carbon::parse($this->input('dob'))->age < $min) {
                $v->errors()->add('dob', $this->input('gender') === 'female' ? __('शादी की क़ानूनी न्यूनतम उम्र लड़की के लिए :min साल है।', ['min' => $min]) : __('शादी की क़ानूनी न्यूनतम उम्र लड़के के लिए :min साल है।', ['min' => $min]));
            }
        }];
    }

    /** Server is the authority on rashi: derived from nakshatra+pada, or checked against the nakshatra. */
    private function validateKundli(Validator $v): void
    {
        $e = $v->errors();
        if ($e->hasAny(['rashi', 'nakshatra', 'nakshatra_pada'])) {
            return;
        }
        $nak = $this->input('nakshatra');
        $pada = $this->input('nakshatra_pada');
        $rashi = $this->input('rashi');
        if ($pada && ! $nak) {
            $e->add('nakshatra', __('चरण के साथ नक्षत्र भी चुनिए।'));

            return;
        }
        if (! $nak || ! $rashi) {
            return;
        }
        if ($pada) {
            $derived = O::rashiFromNakshatraPada($nak, $pada);
            if ($derived !== $rashi) {
                $e->add('rashi', __('राशि, नक्षत्र और चरण से मेल नहीं खाती। सही राशि: :rashi।', ['rashi' => O::rashis()[$derived] ?? '']));
            }
        } elseif (! in_array($rashi, O::rashisForNakshatra($nak), true)) {
            $e->add('rashi', __('यह नक्षत्र इस राशि में नहीं आता। राशि और नक्षत्र दोबारा देखिए।'));
        }
    }

    /** Validated data without the non-column keys. */
    public function biodataData(): array
    {
        $d = collect($this->validated())->except(['save_draft'])->all();
        if (! empty($d['nakshatra']) && ! empty($d['nakshatra_pada'])) {
            $d['rashi'] = O::rashiFromNakshatraPada($d['nakshatra'], $d['nakshatra_pada']);
        }
        if (array_key_exists('nakshatra', $d) && empty($d['nakshatra'])) {
            $d['nakshatra_pada'] = null;
        }
        foreach (['brothers', 'brothers_married', 'sisters', 'sisters_married'] as $k) {
            $d[$k] = (int) ($d[$k] ?? 0);
        }

        return $d;
    }
}
