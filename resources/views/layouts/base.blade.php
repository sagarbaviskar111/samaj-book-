<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <script>(function(){var k='sb_fs',m={1:'100%',2:'112.5%',3:'125%'};function r(){var v=1;try{v=parseInt(localStorage.getItem(k),10)||1}catch(e){}return v>=1&&v<=3?v:1}function a(n){document.documentElement.style.fontSize=m[n];document.documentElement.setAttribute('data-fs',n);var b=document.querySelectorAll('[data-fs-btn]');for(var i=0;i<b.length;i++){b[i].setAttribute('aria-pressed',String(+b[i].getAttribute('data-fs-btn')===n))}}window.sbFs=function(n){try{localStorage.setItem(k,n)}catch(e){}a(n)};window.sbFsStep=function(d){window.sbFs(Math.min(3,Math.max(1,r()+d)))};a(r());document.addEventListener('DOMContentLoaded',function(){a(r())})})();</script>
    <title>{{ $title ?? config('app.name') }} — {{ config('app.name') }}</title>
    <meta name="theme-color" content="#701a24">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700;800&family=Tiro+Devanagari+Hindi:ital@0;1&family=Yatra+One&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    @yield('body')
    <x-install-prompt />
    @if(\App\Support\DevLogin::enabled() && auth()->check())
        @include('partials.dev-switcher')
    @endif
    @include('partials.screen-guard')
    @stack('scripts')
</body>
</html>
