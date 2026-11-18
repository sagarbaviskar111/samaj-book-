@use('App\Support\BiodataOptions', 'O')
@php
    $steps = ['व्यक्तिगत जानकारी', 'पढ़ाई और काम', 'परिवार', 'समाज और पता', 'अपने बारे में और संपर्क'];
    $stepIcons = [
        'M15.5 7.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM5 20a7 7 0 0 1 14 0',
        'M4 8h16v11H4V8Zm5 0V5h6v3M4 13h16',
        'M17 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-3A3.5 3.5 0 0 0 7 18.5V20M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        'M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
        'M8 10h8M8 14h5M5 4h14a1 1 0 0 1 1 1v14l-4-3H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z',
    ];
    $selfOnly = ['gender' => old('gender', $biodata->gender)];
@endphp

<form method="POST" action="{{ $action }}" id="bio-form" class="mx-auto max-w-3xl space-y-4" novalidate>
    @csrf
    @if($method !== 'POST') @method($method) @endif

    {{-- progress: sticky stepper --}}
    <div class="card-pad sticky top-[57px] z-20 !py-3 lg:top-[72px]" id="bio-progress">
        <div class="flex items-center justify-between gap-2 font-semibold text-ink-800">
            <span id="bio-step-label" class="font-display text-base font-bold text-ink-900">हर चरण भरिए</span>
            <span id="bio-step-count" class="hidden rounded-full bg-gold-100 px-3 py-1 text-base font-bold text-ink-900"></span>
        </div>
        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-ink-100"><div id="bio-bar" class="h-full w-full rounded-full bg-gradient-to-r from-kumkum-500 to-gold-400 transition-all duration-500"></div></div>
        <div class="mt-3 hidden items-start justify-between gap-1" id="bio-tabs">
            @foreach($steps as $i => $s)
                <button type="button" data-goto="{{ $i }}" aria-label="चरण {{ $i + 1 }}: {{ $s }}" class="group flex min-h-12 flex-1 flex-col items-center gap-1 rounded-xl px-1 py-1 text-ink-700 transition hover:bg-ink-50">
                    <span data-dot class="grid h-10 w-10 place-items-center rounded-full bg-ink-50 text-ink-700 ring-1 ring-ink-100 transition">
                        <svg class="pointer-events-none h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $stepIcons[$i] }}"/></svg>
                    </span>
                    <span class="pointer-events-none hidden text-sm font-semibold leading-tight sm:block">{{ $s }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-xl bg-rose-50 px-4 py-3 text-base font-semibold text-rose-800 ring-1 ring-rose-200" role="alert">कुछ जानकारी सही नहीं है। लाल निशान वाली जगह देखिए और ठीक कीजिए।</div>
    @endif

    {{-- 1 Personal --}}
    <section data-step="0" class="card-pad">
        @include("biodata.partials.step-head", ["n" => 1, "title" => "व्यक्तिगत जानकारी", "help" => "नाम, जन्म तारीख़, कद जैसी बुनियादी जानकारी। सही और सच्ची भरिए।", "icon" => "person"])
        <div class="grid gap-4 sm:grid-cols-2">
            @include('biodata.partials.field', ['name' => 'added_by', 'label' => 'यह बायोडाटा किसने बनाया?', 'type' => 'select', 'options' => O::addedBy(), 'required' => true, 'seg' => true, 'hint' => 'अपना बायोडाटा खुद बना रहे हैं तो "खुद" चुनिए।'])
            @include('biodata.partials.field', ['name' => 'gender', 'label' => 'लड़का या लड़की?', 'type' => 'select', 'options' => O::genders(), 'required' => true, 'seg' => true])
            @include('biodata.partials.field', ['name' => 'name', 'label' => 'पूरा नाम', 'required' => true, 'span2' => true])
            @include('biodata.partials.field', ['name' => 'dob', 'label' => 'जन्म तारीख़', 'type' => 'date', 'required' => true, 'hint' => 'लड़की की उम्र कम से कम 18 और लड़के की कम से कम 21 साल होनी चाहिए।'])
            @include('biodata.partials.field', ['name' => 'marital_status', 'label' => 'वैवाहिक स्थिति', 'type' => 'select', 'options' => O::maritalStatuses(), 'required' => true, 'seg' => true])
            @include('biodata.partials.field', ['name' => 'height_cm', 'label' => 'कद (ऊँचाई)', 'type' => 'select', 'options' => O::heights()])
            @include('biodata.partials.field', ['name' => 'weight_kg', 'label' => 'वज़न (किलो)', 'type' => 'number', 'min' => 30, 'max' => 200])
            @include('biodata.partials.field', ['name' => 'complexion', 'label' => 'रंग', 'type' => 'select', 'options' => O::complexions()])
            @include('biodata.partials.field', ['name' => 'blood_group', 'label' => 'ब्लड ग्रुप', 'type' => 'select', 'options' => O::bloodGroups()])
            @include('biodata.partials.field', ['name' => 'manglik', 'label' => 'मांगलिक (Manglik)', 'type' => 'select', 'options' => ['1' => 'हाँ', '0' => 'नहीं'], 'seg' => true])
            @include('biodata.partials.field', ['name' => 'disability', 'label' => 'कोई शारीरिक कमी (अगर हो)', 'placeholder' => 'नहीं है तो खाली छोड़िए'])
            @include('biodata.partials.field', ['name' => 'birth_time', 'label' => 'जन्म का समय (कुंडली के लिए)', 'type' => 'time'])
            @include('biodata.partials.field', ['name' => 'birth_place', 'label' => 'जन्म स्थान'])
            <div class="sm:col-span-2 rounded-2xl bg-gold-50 p-4 ring-1 ring-gold-200" id="kundli-block">
                <p class="text-base font-bold text-ink-900">&#128302; कुंडली की जानकारी (ज़रूरी नहीं)</p>
                <p class="mb-3 text-sm text-ink-700">कुंडली में लिखा होता है। पता न हो तो खाली छोड़िए। यह सिर्फ़ रिश्ता मंज़ूर होने के बाद सामने वाले को दिखती है।</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    @include('biodata.partials.field', ['name' => 'nakshatra', 'label' => 'नक्षत्र', 'type' => 'select', 'options' => O::nakshatras()])
                    @include('biodata.partials.field', ['name' => 'nakshatra_pada', 'label' => 'चरण (पद)', 'type' => 'select', 'options' => [1 => '1', 2 => '2', 3 => '3', 4 => '4']])
                    @include('biodata.partials.field', ['name' => 'rashi', 'label' => 'राशि', 'type' => 'select', 'options' => O::rashis(), 'hint' => 'नक्षत्र और चरण चुनने पर अपने आप भर जाती है।'])
                </div>
            </div>
            <script>
            (function () {
                var nak = document.getElementById('f-nakshatra'), pada = document.getElementById('f-nakshatra_pada'), rashi = document.getElementById('f-rashi');
                if (!nak || !pada || !rashi) return;
                var naks = {!! json_encode(array_keys(O::nakshatras())) !!}, rashis = {!! json_encode(array_keys(O::rashis())) !!};
                function sync() {
                    var n = naks.indexOf(nak.value), p = parseInt(pada.value, 10);
                    if (n < 0) return;
                    if (p >= 1 && p <= 4) { rashi.value = rashis[Math.floor((n * 4 + p - 1) / 9)]; return; }
                    var a = Math.floor(n * 4 / 9), b = Math.floor((n * 4 + 3) / 9);
                    if (a === b) rashi.value = rashis[a];
                }
                nak.addEventListener('change', sync); pada.addEventListener('change', sync);
            })();
            </script>
        </div>
    </section>

    {{-- 2 Education & work --}}
    <section data-step="1" class="card-pad">
        @include("biodata.partials.step-head", ["n" => 2, "title" => "पढ़ाई और काम", "help" => "कितना पढ़े हैं और क्या काम करते हैं। आमदनी बताना ज़रूरी नहीं, चाहें तो छिपा सकते हैं।", "icon" => "work"])
        <div class="grid gap-4 sm:grid-cols-2">
            @include('biodata.partials.field', ['name' => 'education_level', 'label' => 'पढ़ाई कितनी है?', 'type' => 'select', 'options' => O::educationLevels()])
            @include('biodata.partials.field', ['name' => 'education', 'label' => 'डिग्री / कॉलेज का नाम', 'placeholder' => 'जैसे: B.Com, XYZ कॉलेज'])
            @include('biodata.partials.field', ['name' => 'occupation_type', 'label' => 'काम का प्रकार', 'type' => 'select', 'options' => O::occupationTypes()])
            @include('biodata.partials.field', ['name' => 'occupation', 'label' => 'क्या काम करते हैं? (पद / व्यवसाय)'])
            @include('biodata.partials.field', ['name' => 'company', 'label' => 'कंपनी या संस्था का नाम'])
            @include('biodata.partials.field', ['name' => 'income_range', 'label' => 'सालाना आमदनी (ज़रूरी नहीं)', 'type' => 'select', 'options' => O::incomeRanges()])
            <label class="flex min-h-12 items-center gap-3 text-base text-ink-900 sm:col-span-2">
                <input type="hidden" name="income_private" value="0">
                <input type="checkbox" name="income_private" value="1" class="h-6 w-6 shrink-0 rounded border-ink-300 text-ink-600" @checked(old('income_private', $biodata->income_private ?? true))>
                रिश्ता मंज़ूर होने तक मेरी आमदनी छिपाकर रखिए
            </label>
        </div>
    </section>

    {{-- 3 Family --}}
    <section data-step="2" class="card-pad">
        @include("biodata.partials.step-head", ["n" => 3, "title" => "परिवार", "help" => "परिवार के बारे में बताइए। रिश्ते के लिए यह बहुत काम आता है।", "icon" => "family"])
        <div class="grid gap-4 sm:grid-cols-2">
            @include('biodata.partials.field', ['name' => 'father_name', 'label' => 'पिता का नाम'])
            @include('biodata.partials.field', ['name' => 'father_occupation', 'label' => 'पिता का काम'])
            @include('biodata.partials.field', ['name' => 'mother_name', 'label' => 'माता का नाम'])
            @include('biodata.partials.field', ['name' => 'mother_occupation', 'label' => 'माता का काम'])
            @include('biodata.partials.field', ['name' => 'brothers', 'label' => 'कितने भाई हैं?', 'type' => 'number', 'max' => 15])
            @include('biodata.partials.field', ['name' => 'brothers_married', 'label' => 'इनमें कितने भाई विवाहित हैं?', 'type' => 'number', 'max' => 15])
            @include('biodata.partials.field', ['name' => 'sisters', 'label' => 'कितनी बहनें हैं?', 'type' => 'number', 'max' => 15])
            @include('biodata.partials.field', ['name' => 'sisters_married', 'label' => 'इनमें कितनी बहनें विवाहित हैं?', 'type' => 'number', 'max' => 15])
            @include('biodata.partials.field', ['name' => 'family_type', 'label' => 'परिवार का प्रकार', 'type' => 'select', 'options' => O::familyTypes(), 'seg' => true])
            @include('biodata.partials.field', ['name' => 'mama_gotra', 'label' => 'मामा का गोत्र'])
            @include('biodata.partials.field', ['name' => 'mama_village', 'label' => 'मामा का गाँव'])
        </div>
    </section>

    {{-- 4 Samaj & location --}}
    <section data-step="3" class="card-pad">
        @include("biodata.partials.step-head", ["n" => 4, "title" => "समाज और पता", "help" => "गोत्र और रहने की जगह। लोग खोजते समय यही जानकारी इस्तेमाल करते हैं।", "icon" => "pin"])
        <div class="grid gap-4 sm:grid-cols-2">
            @include('biodata.partials.field', ['name' => 'gotra', 'label' => 'गोत्र'])
            @include('biodata.partials.field', ['name' => 'upjati', 'label' => 'उपजाति'])
            @include('biodata.partials.field', ['name' => 'village', 'label' => 'गाँव'])
            @include('biodata.partials.field', ['name' => 'tehsil', 'label' => 'तहसील'])
            @include('biodata.partials.field', ['name' => 'district', 'label' => 'ज़िला (District)', 'list' => O::districts(), 'hint' => 'लिखना शुरू कीजिए, नाम सुझाव में आ जाएगा।'])
            @include('biodata.partials.field', ['name' => 'state', 'label' => 'राज्य (State)', 'list' => O::states()])
            @include('biodata.partials.field', ['name' => 'current_city', 'label' => 'अभी किस शहर में रहते हैं?'])
            @include('biodata.partials.field', ['name' => 'area', 'label' => 'इलाक़ा / मोहल्ला'])
        </div>
    </section>

    {{-- 5 About, expectation, contact, privacy --}}
    <section data-step="4" class="card-pad">
        @include("biodata.partials.step-head", ["n" => 5, "title" => "अपने बारे में और संपर्क", "help" => "अपने बारे में लिखिए और मोबाइल नंबर डालिए। नंबर तभी दिखेगा जब कोई रिश्ता मंज़ूर होगा।", "icon" => "about"])
        <div class="grid gap-4 sm:grid-cols-2">
            @include('biodata.partials.field', ['name' => 'about', 'label' => 'अपने बारे में', 'type' => 'textarea', 'span2' => true, 'placeholder' => 'स्वभाव, शौक़, सोच...'])
            @include('biodata.partials.field', ['name' => 'expectation', 'label' => 'जीवनसाथी से अपेक्षा', 'type' => 'textarea', 'span2' => true, 'placeholder' => 'कैसा जीवनसाथी चाहते हैं?'])
            @include('biodata.partials.field', ['name' => 'pref_age_min', 'label' => 'जीवनसाथी की उम्र: कम से कम', 'type' => 'number', 'min' => 18, 'max' => 70])
            @include('biodata.partials.field', ['name' => 'pref_age_max', 'label' => 'जीवनसाथी की उम्र: ज़्यादा से ज़्यादा', 'type' => 'number', 'min' => 18, 'max' => 70])
            @include('biodata.partials.field', ['name' => 'pref_education_level', 'label' => 'जीवनसाथी की पढ़ाई', 'type' => 'select', 'options' => O::educationLevels()])
            @include('biodata.partials.field', ['name' => 'pref_district', 'label' => 'जीवनसाथी का ज़िला', 'list' => O::districts()])
        </div>

        <div class="mt-5 rounded-2xl bg-ink-50 p-4 ring-1 ring-ink-100">
            <h3 class="mb-3 text-base font-bold text-ink-900">संपर्क और गोपनीयता</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                @include('biodata.partials.field', ['name' => 'contact_phone', 'label' => 'मोबाइल / WhatsApp नंबर', 'type' => 'tel', 'required' => true, 'hint' => 'यह नंबर सिर्फ़ रिश्ता मंज़ूर होने के बाद सामने वाले को दिखेगा।'])
                @include('biodata.partials.field', ['name' => 'photo_privacy', 'label' => 'आपकी फ़ोटो कौन देख सके?', 'type' => 'select', 'options' => O::photoPrivacies(), 'required' => true, 'seg' => true])
                <label class="flex items-start gap-3 text-base text-ink-900 sm:col-span-2">
                    <input type="hidden" name="requests_to_parent" value="0">
                    <input type="checkbox" name="requests_to_parent" value="1" class="mt-0.5 h-6 w-6 shrink-0 rounded border-ink-300 text-ink-600" @checked(old('requests_to_parent', $biodata->requests_to_parent))>
                    <span>सारी पसंद (रिश्ते के अनुरोध) माता-पिता / परिवार के खाते में भेजिए, मेरे खाते में नहीं</span>
                </label>
            </div>
        </div>
    </section>


    <div class="sticky bottom-20 z-20 flex flex-wrap items-center justify-between gap-2 rounded-2xl glass p-3 shadow-glow lg:bottom-4">
        <button type="button" id="bio-prev" class="btn-ghost hidden !min-h-12">&larr; पीछे</button>
        <div class="ml-auto flex flex-wrap gap-2">
            @if(! $biodata->exists || $biodata->status === 'draft')
                <button type="submit" name="save_draft" value="1" class="btn-ghost !min-h-12" formnovalidate>ड्राफ़्ट सेव करें</button>
            @endif
            <button type="button" id="bio-next" class="btn-primary hidden !min-h-12 !px-6 !text-lg">आगे बढ़ें &rarr;</button>
            <button type="submit" id="bio-submit" class="btn-gold !min-h-12 !px-6 !text-lg">{{ $biodata->exists ? 'सेव करें' : 'मंज़ूरी के लिए भेजें' }}</button>
        </div>
    </div>
</form>

<script>
(function () {
    var form = document.getElementById('bio-form');
    if (!form) return;
    var secs = [].slice.call(form.querySelectorAll('[data-step]'));
    var cur = 0, total = secs.length;
    var prev = document.getElementById('bio-prev'), next = document.getElementById('bio-next'), submit = document.getElementById('bio-submit');
    var bar = document.getElementById('bio-bar'), lbl = document.getElementById('bio-step-label'), cnt = document.getElementById('bio-step-count');
    var names = {!! json_encode($steps) !!};
    var tabs = document.getElementById('bio-tabs');
    var exists = {{ $biodata->exists ? 'true' : 'false' }};
    var maxSeen = 0;

    // Segmented controls: progressive enhancement over <select data-seg>; the select stays the source of truth.
    [].forEach.call(form.querySelectorAll('select[data-seg]'), function (sel) {
        var wrap = document.createElement('div');
        wrap.className = 'bd-seg'; wrap.setAttribute('role', 'group');
        function paint() { [].forEach.call(wrap.children, function (b) { b.setAttribute('aria-pressed', b.getAttribute('data-v') === sel.value ? 'true' : 'false'); }); }
        [].forEach.call(sel.options, function (o) {
            if (o.value === '') return;
            var b = document.createElement('button');
            b.type = 'button'; b.textContent = o.textContent; b.setAttribute('data-v', o.value);
            b.addEventListener('click', function () {
                sel.value = (sel.value === o.value && !sel.required) ? '' : o.value;
                sel.dispatchEvent(new Event('change', {bubbles: true})); paint();
            });
            wrap.appendChild(b);
        });
        sel.style.cssText = 'position:absolute;opacity:0;pointer-events:none;height:1px;width:1px';
        sel.setAttribute('tabindex', '-1');
        sel.parentNode.insertBefore(wrap, sel.nextSibling);
        sel.addEventListener('change', paint); paint();
    });

    function show(i) {
        cur = Math.max(0, Math.min(total - 1, i));
        maxSeen = Math.max(maxSeen, cur);
        secs.forEach(function (s, k) { s.style.display = k === cur ? '' : 'none'; });
        if (secs[cur].classList) { secs[cur].classList.remove('bd-fade-up'); void secs[cur].offsetWidth; secs[cur].classList.add('bd-fade-up'); }
        prev.classList.toggle('hidden', cur === 0);
        next.classList.toggle('hidden', cur === total - 1);
        submit.classList.toggle('hidden', cur !== total - 1 && !exists);
        bar.style.width = ((cur + 1) / total * 100) + '%';
        lbl.textContent = names[cur];
        cnt.textContent = 'चरण ' + (cur + 1) + ' / ' + total; cnt.classList.remove('hidden');
        tabs.classList.remove('hidden'); tabs.classList.add('flex');
        [].forEach.call(tabs.children, function (b, k) {
            var d = b.querySelector('[data-dot]');
            var active = k === cur, done = k < cur || (exists && k !== cur);
            d.className = 'grid h-10 w-10 place-items-center rounded-full ring-1 transition ' +
                (active ? 'bg-gradient-to-br from-kumkum-600 to-kumkum-400 text-white ring-ink-300 shadow-glow scale-110' : (done ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-ink-50 text-ink-700 ring-ink-100'));
            b.className = b.className.replace(/\btext-ink-(700|900)\b/g, '') + (active ? ' text-ink-900' : ' text-ink-700');
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function valid() {
        var ok = true;
        [].forEach.call(secs[cur].querySelectorAll('input,select,textarea'), function (el) {
            if (!el.checkValidity()) { if (ok) { if (el.tagName === 'SELECT' && el.hasAttribute('data-seg')) { el.parentNode.scrollIntoView({block: 'center'}); } el.reportValidity(); } ok = false; }
        });
        return ok;
    }
    next.addEventListener('click', function () { if (valid()) show(cur + 1); });
    prev.addEventListener('click', function () { show(cur - 1); });
    tabs.addEventListener('click', function (e) { var t = e.target.closest('[data-goto]'); if (t) show(parseInt(t.getAttribute('data-goto'), 10)); });
    form.addEventListener('submit', function (e) {
        var btn = e.submitter;
        if (btn && btn.name === 'save_draft') return;
        for (var k = 0; k < total; k++) {
            var bad = [].some.call(secs[k].querySelectorAll('input,select,textarea'), function (el) { return !el.checkValidity(); });
            if (bad) { e.preventDefault(); show(k); valid(); return; }
        }
        submit.disabled = true; submit.textContent = 'सेव हो रहा है...';
    });
    // Browser pop-up messages in Hindi.
    form.addEventListener('invalid', function (e) {
        var el = e.target; el.setCustomValidity('');
        if (el.validity.valueMissing) el.setCustomValidity(el.tagName === 'SELECT' ? 'कृपया एक विकल्प चुनिए।' : 'यह जानकारी भरना ज़रूरी है।');
        else if (el.validity.rangeUnderflow || el.validity.rangeOverflow) el.setCustomValidity('कृपया सही संख्या डालिए।');
    }, true);
    ['input', 'change'].forEach(function (ev) { form.addEventListener(ev, function (e) { if (e.target.setCustomValidity) e.target.setCustomValidity(''); }, true); });
    // jump to the first step that has a server-side error
    var first = 0;
    for (var k = 0; k < total; k++) { if (secs[k].querySelector('.field-error')) { first = k; break; } }
    show(first);
})();
</script>
