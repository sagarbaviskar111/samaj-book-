@extends('pages._shell')
@section('heading', __('अक्सर पूछे जाने वाले प्रश्न'))
@section('sub', __('आपके सवाल, हमारे जवाब।'))

@section('page')
@php
    $faqs = [
        ['क्या यह फ्री है?', 'हाँ, समाज के सदस्यों के लिए निःशुल्क।'],
        ['रजिस्टर कैसे करें?', 'फ़ोन नंबर और बुनियादी जानकारी से रजिस्टर करें। एडमिन अप्रूवल के बाद पूरा एक्सेस मिलेगा।'],
        ['मेरा नंबर कौन देख सकता है?', 'जब तक आप पसंद स्वीकार नहीं करते, आपका संपर्क विवरण छिपा रहता है।'],
        ['क्या मेरी फ़ोटो सुरक्षित है?', 'हाँ। फ़ोटो ब्लर और वॉटरमार्क का विकल्प है, और प्रोफ़ाइल केवल अप्रूव्ड सदस्यों को दिखती है।'],
        ['न्यूनतम आयु क्या है?', 'लड़की 18 वर्ष, लड़का 21 वर्ष।'],
        ['क्या आधार की ज़रूरत है?', 'नहीं। हम आधार नंबर नहीं माँगते और न सेव करते हैं।'],
        ['मेरा डेटा कैसे हटाऊँ?', 'खाता सेटिंग से हटाने का अनुरोध करें या एडमिन से संपर्क करें। नियमों के अनुसार आपकी जानकारी हटा दी जाएगी।'],
        ['क्या बायोडाटा PDF में मिलेगा?', 'हाँ, आप अपना बायोडाटा PDF डाउनलोड और शेयर कर सकते हैं।'],
    ];
@endphp
<div class="hr-faq space-y-3">
    @foreach ($faqs as [$q, $a])
        <details class="overflow-hidden rounded-2xl border border-gold-200 bg-cream/70 transition">
            <summary class="flex min-h-[3.5rem] cursor-pointer items-center justify-between gap-3 px-4 py-3 text-lg font-bold text-ink-900">
                <span>{{ __($q) }}</span>
                <span class="hr-plus grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gold-100 text-kumkum-700"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
            </summary>
            <p class="px-4 pb-4 text-base leading-relaxed">{{ __($a) }}</p>
        </details>
    @endforeach
</div>
@endsection
