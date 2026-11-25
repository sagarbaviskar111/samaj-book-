<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function terms() { return view('pages.terms', ['title' => __('Terms & Conditions / नियम और शर्तें')]); }
    public function privacy() { return view('pages.privacy', ['title' => __('Privacy Policy / गोपनीयता नीति')]); }
    public function about() { return view('pages.about', ['title' => __('About us / हमारे बारे में')]); }
    public function faq() { return view('pages.faq', ['title' => __('FAQ / अक्सर पूछे जाने वाले प्रश्न')]); }
}
