<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\Interest;
use App\Models\Shortlist;
use App\Services\MatchScore;
use App\Support\AccessLevel;
use App\Support\BiodataOptions;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BiodataSearchController extends Controller
{
    private const PER_PAGE = 12;
    private const MATCH_SORT_CAP = 300;

    public function index(Request $request)
    {
        $user = $request->user();
        $level = AccessLevel::for($user);
        if ($level === AccessLevel::LITE) {
            return $this->lite($request);
        }
        $own = Biodata::where('user_id', $user->id)->latest('id')->get(['id', 'gender', 'status']);
        $lookingFor = $level === AccessLevel::VIEW_ONLY ? AccessLevel::lookingFor($user) : null;

        $for = $request->query('for');
        if (! in_array($for, ['bride', 'groom'], true)) {
            $latest = $own->first();
            $for = $latest ? ($latest->gender === 'male' ? 'bride' : 'groom') : ($lookingFor?->looking_for);
        }
        // Looking-for defaults (only when the member did not pick any filter themselves).
        if ($lookingFor && ! $request->query->has('nd') && ! $request->hasAny(['age_min', 'age_max', 'education_level', 'district', 'q', 'sort'])) {
            $request->query->add(array_filter([
                'age_min' => $lookingFor->age_min, 'age_max' => $lookingFor->age_max,
                'education_level' => $lookingFor->education_level, 'district' => $lookingFor->district,
            ], fn ($v) => $v !== null) + ['nd' => 1]);
        }

        $opts = [
            'education' => BiodataOptions::educationLevels(),
            'occupation' => BiodataOptions::occupationTypes(),
            'marital' => BiodataOptions::maritalStatuses(),
            'income' => BiodataOptions::incomeRanges(),
        ];

        if (! $for) {
            return view('search.index', [
                'for' => null, 'biodatas' => new LengthAwarePaginator([], 0, self::PER_PAGE),
                'opts' => $opts, 'chips' => [], 'shortlistIds' => [], 'interestMap' => [], 'matches' => [],
                'f' => [], 'sort' => 'newest', 'hasOwn' => $own->isNotEmpty(), 'lite' => false,
            ]);
        }

        $gender = $for === 'bride' ? 'female' : 'male';
        $f = $this->filters($request);
        $sort = in_array($request->query('sort'), ['newest', 'age', 'match'], true) ? $request->query('sort') : 'newest';

        $query = Biodata::approved()->where('gender', $gender)->whereNotIn('user_id', [$user->id])->with('primaryPhoto');
        $this->applyFilters($query, $f, $user);

        // Viewer's own approved biodata of the opposite gender is used for match % and expectations.
        $viewerBiodata = Biodata::approved()->where('user_id', $user->id)
            ->where('gender', $gender === 'female' ? 'male' : 'female')->latest('id')->first();

        $matches = [];
        if ($sort === 'match' && $viewerBiodata) {
            $all = $query->orderByDesc('id')->limit(self::MATCH_SORT_CAP)->get();
            foreach ($all as $b) {
                $matches[$b->id] = MatchScore::between($viewerBiodata, $b);
            }
            $sorted = $all->sortByDesc(fn ($b) => $matches[$b->id])->values();
            $page = LengthAwarePaginator::resolveCurrentPage();
            $biodatas = new LengthAwarePaginator(
                $sorted->forPage($page, self::PER_PAGE)->values(), $sorted->count(), self::PER_PAGE, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $query->when($sort === 'age', fn ($q) => $q->orderByDesc('dob'), fn ($q) => $q->orderByDesc('id'));
            $biodatas = $query->paginate(self::PER_PAGE)->withQueryString();
            if ($viewerBiodata) {
                foreach ($biodatas as $b) {
                    $matches[$b->id] = MatchScore::between($viewerBiodata, $b);
                }
            }
        }

        $ids = $biodatas->pluck('id')->all();

        return view('search.index', [
            'for' => $for,
            'biodatas' => $biodatas,
            'opts' => $opts,
            'f' => $f,
            'sort' => $sort,
            'chips' => $this->chips($request, $f, $opts),
            'shortlistIds' => Shortlist::where('user_id', $user->id)->whereIn('biodata_id', $ids)->pluck('biodata_id')->all(),
            'interestMap' => self::interestMapFor($user->id, $ids),
            'matches' => $matches,
            'hasOwn' => $own->isNotEmpty(), 'lite' => false,
        ]);
    }

    /**
     * Locked mode (member has no biodata): NO biodata is listed. Only counts and district names are shown,
     * with a push to create their own biodata first.
     */
    private function lite(Request $request)
    {
        $for = $request->query('for') === 'groom' ? 'groom' : 'bride';
        $base = fn (string $gender) => Biodata::approved()->where('gender', $gender)->whereNotIn('user_id', [$request->user()->id]);

        $labels = BiodataOptions::districtLabels();
        $byDistrict = $base($for === 'bride' ? 'female' : 'male')->whereNotNull('district')->where('district', '!=', '')
            ->selectRaw('district, count(*) as c')->groupBy('district')->orderByDesc('c')->pluck('c', 'district')
            ->mapWithKeys(fn ($c, $d) => [($labels[$d] ?? $d) => $c])->all();

        return view('search.locked', [
            'for' => $for,
            'total' => array_sum($byDistrict) + $base($for === 'bride' ? 'female' : 'male')->where(fn ($q) => $q->whereNull('district')->orWhere('district', ''))->count(),
            'brides' => $base('female')->count(),
            'grooms' => $base('male')->count(),
            'byDistrict' => $byDistrict,
        ]);
    }

    /** candidate biodata id => ['id','status','dir' (sent|received)] for the given user. */
    public static function interestMapFor(int $userId, array $biodataIds): array
    {
        if (! $biodataIds) {
            return [];
        }
        $map = [];
        $rows = Interest::where(function ($q) use ($userId, $biodataIds) {
            $q->where(fn ($w) => $w->where('from_user_id', $userId)->whereIn('to_biodata_id', $biodataIds))
                ->orWhere(fn ($w) => $w->where('to_user_id', $userId)->whereIn('from_biodata_id', $biodataIds));
        })->get();
        foreach ($rows as $i) {
            if ($i->from_user_id === $userId) {
                $map[$i->to_biodata_id] = ['id' => $i->id, 'status' => $i->status, 'dir' => 'sent'];
            } else {
                $map[$i->from_biodata_id] ??= ['id' => $i->id, 'status' => $i->status, 'dir' => 'received'];
            }
        }

        return $map;
    }

    private function filters(Request $r): array
    {
        $r->validate([
            'age_min' => 'nullable|integer|min:18|max:80', 'age_max' => 'nullable|integer|min:18|max:80',
            'height_min' => 'nullable|integer|min:100|max:230', 'height_max' => 'nullable|integer|min:100|max:230',
            'q' => 'nullable|string|max:100',
        ]);
        $text = fn ($k) => ($v = trim((string) $r->query($k, ''))) !== '' ? mb_substr($v, 0, 100) : null;
        $num = fn ($k) => $r->filled($k) && is_numeric($r->query($k)) ? (int) $r->query($k) : null;

        return [
            'q' => $text('q'),
            'age_min' => $num('age_min'), 'age_max' => $num('age_max'),
            'height_min' => $num('height_min'), 'height_max' => $num('height_max'),
            'education_level' => $text('education_level'), 'occupation_type' => $text('occupation_type'),
            'marital_status' => $text('marital_status'), 'income_range' => $text('income_range'),
            'manglik' => in_array($r->query('manglik'), ['0', '1'], true) ? $r->query('manglik') : null,
            'district' => $text('district'), 'area' => $text('area'), 'state' => $text('state'),
            'current_city' => $text('current_city'), 'gotra' => $text('gotra'),
            'ex_gotra' => $r->query('ex_gotra', '1') !== '0',
            'verified' => $r->boolean('verified'), 'with_photo' => $r->boolean('with_photo'),
            'new_days' => in_array((int) $r->query('new_days'), [7, 30], true) ? (int) $r->query('new_days') : null,
        ];
    }

    private function applyFilters($q, array $f, $user): void
    {
        $like = fn (string $v) => '%'.addcslashes($v, '%_\\').'%';

        if ($f['q']) {
            $q->where(function ($w) use ($f, $like) {
                foreach (['name', 'area', 'village', 'occupation'] as $col) {
                    $w->orWhere($col, 'like', $like($f['q']));
                }
            });
        }
        if ($f['age_min']) {
            $q->where('dob', '<=', now()->subYears($f['age_min'])->toDateString());
        }
        if ($f['age_max']) {
            $q->where('dob', '>', now()->subYears($f['age_max'] + 1)->toDateString());
        }
        if ($f['height_min']) {
            $q->where('height_cm', '>=', $f['height_min']);
        }
        if ($f['height_max']) {
            $q->where('height_cm', '<=', $f['height_max']);
        }
        foreach (['education_level', 'occupation_type', 'marital_status', 'income_range', 'district', 'state', 'current_city'] as $col) {
            if ($f[$col]) {
                $q->where($col, $f[$col]);
            }
        }
        if ($f['manglik'] !== null) {
            $q->where('manglik', $f['manglik'] === '1');
        }
        if ($f['area']) {
            $q->where('area', 'like', $like($f['area']));
        }
        if ($f['gotra']) {
            $q->where('gotra', $f['gotra']);
        }
        if ($f['ex_gotra'] && $user->gotra) {
            $q->where(fn ($w) => $w->whereNull('gotra')->orWhere('gotra', '!=', $user->gotra));
        }
        if ($f['verified']) {
            $q->where('verified', true);
        }
        if ($f['with_photo']) {
            $q->whereHas('primaryPhoto');
        }
        if ($f['new_days']) {
            $q->where('created_at', '>=', now()->subDays($f['new_days']));
        }
    }

    /** Active-filter chips with a URL that removes just that filter. */
    private function chips(Request $r, array $f, array $opts): array
    {
        $labels = [
            'q' => fn ($v) => '"'.$v.'"',
            'age_min' => fn ($v) => __('उम्र :v से ज़्यादा', ['v' => $v]), 'age_max' => fn ($v) => __('उम्र :v तक', ['v' => $v]),
            'height_min' => fn ($v) => __('कद :v cm से ऊपर', ['v' => $v]), 'height_max' => fn ($v) => __('कद :v cm तक', ['v' => $v]),
            'education_level' => fn ($v) => __('पढ़ाई: :v', ['v' => $opts['education'][$v] ?? $v]),
            'occupation_type' => fn ($v) => __('नौकरी: :v', ['v' => $opts['occupation'][$v] ?? $v]),
            'marital_status' => fn ($v) => $opts['marital'][$v] ?? $v,
            'income_range' => fn ($v) => __('आमदनी: :v', ['v' => $opts['income'][$v] ?? $v]),
            'manglik' => fn ($v) => $v === '1' ? __('मांगलिक') : __('मांगलिक नहीं'),
            'district' => fn ($v) => __('ज़िला: :v', ['v' => BiodataOptions::districtLabels()[$v] ?? $v]), 'area' => fn ($v) => __('इलाक़ा: :v', ['v' => $v]), 'state' => fn ($v) => __('राज्य: :v', ['v' => BiodataOptions::states()[$v] ?? $v]),
            'current_city' => fn ($v) => __('शहर: :v', ['v' => $v]), 'gotra' => fn ($v) => __('गोत्र: :v', ['v' => $v]),
            'verified' => fn ($v) => __('सिर्फ़ जाँचे हुए'), 'with_photo' => fn ($v) => __('फ़ोटो वाले'),
            'new_days' => fn ($v) => __('नए (:v दिन)', ['v' => $v]),
        ];
        $chips = [];
        foreach ($labels as $key => $fn) {
            if ($f[$key] !== null && $f[$key] !== false && $f[$key] !== '') {
                $chips[] = ['label' => (string) $fn($f[$key]), 'url' => $r->fullUrlWithoutQuery([$key, 'page'])];
            }
        }
        if (! $f['ex_gotra']) {
            $chips[] = ['label' => __('अपना गोत्र भी शामिल'), 'url' => $r->fullUrlWithoutQuery(['ex_gotra', 'page'])];
        }

        return $chips;
    }
}
