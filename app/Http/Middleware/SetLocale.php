<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public const SUPPORTED = ['hi', 'en', 'mr'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale') ?: $request->user()?->language;
        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'hi';
        }
        app()->setLocale($locale);
        \Illuminate\Support\Carbon::setLocale($locale); // month/day names in the chosen language

        return $next($request);
    }
}
