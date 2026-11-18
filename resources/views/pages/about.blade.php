@extends('pages._shell')
@section('heading', 'हमारे बारे में / About us')
@section('sub', 'समाज का अपना, भरोसेमंद डिजिटल परिवार।')

@section('page')
<section>
    <h2>हमारा उद्देश्य</h2>
    <p>Samaj Book समाज के परिवारों के लिए बनाया गया है, ताकि योग्य जीवनसाथी की तलाश अपनों के बीच, सुरक्षित और सम्मानजनक तरीके से हो सके। इसे समाज की सेवा के लिए बनाया गया है, इसलिए यह समाज के सदस्यों के लिए निःशुल्क है।</p>
    <p>Samaj Book is a privacy-first matrimony and community platform built for our samaj, so that families can find the right match among their own people, safely and respectfully.</p>
</section>
<section>
    <h2>हम क्या करते हैं</h2>
    <ul>
        <li>एडमिन द्वारा जाँचे गए सदस्य और बायोडाटा।</li>
        <li>संपर्क विवरण केवल इंटरेस्ट स्वीकार होने के बाद।</li>
        <li>समाज की खबरें, कार्यक्रम और सफल कहानियाँ एक जगह।</li>
    </ul>
</section>
<section>
    <h2>जुड़ें</h2>
    <p>
        @if (Route::has('register'))<a href="{{ route('register') }}">आज ही रजिस्टर करें</a>@else रजिस्टर करें @endif
        और अपने समाज के परिवार का हिस्सा बनें।
    </p>
</section>
@endsection
