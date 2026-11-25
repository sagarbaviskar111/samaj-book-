<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('लिंक उपलब्ध नहीं') }} · SamajBook</title>
<meta name="robots" content="noindex,nofollow">
<meta property="og:type" content="website">
<meta property="og:site_name" content="SamajBook">
<meta property="og:title" content="{{ __('समाज बुक पर एक बायोडाटा') }}">
<meta property="og:description" content="{{ __('SamajBook पर लॉगिन करके देखिए।') }}">
<style>
    :root { --cream:#fff9ef; --ink:#3b2a20; --kumkum:#96202a; --gold:#ee9e0a; }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; display:grid; place-items:center; background:var(--cream); color:var(--ink); font-family: Mukta, system-ui, sans-serif; padding:16px; }
    .card { max-width:26rem; background:#fff; border-radius:1.5rem; padding:2rem 1.5rem; text-align:center; box-shadow:0 10px 30px rgba(112,26,36,.12); border-top:6px solid var(--gold); }
    h1 { font-size:1.4rem; margin:.5rem 0; color:var(--kumkum); }
    p { font-size:1.0625rem; line-height:1.7; margin:.4rem 0; }
    a.btn { display:inline-block; margin-top:1rem; min-height:48px; line-height:48px; font-size:1.0625rem; padding:0 1.5rem; border-radius:999px; background:var(--kumkum); color:#fff; text-decoration:none; font-weight:700; }
</style></head>
<body>
<main class="card">
    <div aria-hidden="true" style="font-size:2.5rem">&#128279;</div>
    <h1>{{ __('यह लिंक अब उपलब्ध नहीं है') }}</h1>
    <p>{{ __('लिंक ग़लत है, बंद कर दिया गया है, या बायोडाटा अभी मंज़ूर नहीं हुआ है।') }}</p>
    <p>{{ __('नया लिंक भेजने के लिए बायोडाटा के मालिक से कहिए।') }}</p>
    <a class="btn" href="{{ url('/') }}">{{ __('SamajBook पर जाइए') }}</a>
</main>
</body></html>
