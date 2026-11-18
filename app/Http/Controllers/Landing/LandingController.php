<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\Interest;
use App\Models\NewsItem;
use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user) {
            if ($user->isSuperAdmin() && Route::has('superadmin.dashboard')) {
                return redirect()->route('superadmin.dashboard');
            }
            if ($user->isAdmin() && Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->isActive() && Route::has('home')) {
                return redirect()->route('home');
            }
        }

        $stats = Cache::remember('landing.stats', 600, fn () => [
            'members' => User::where('status', 'active')->where('role', 'user')->count(),
            'biodata' => Biodata::where('status', 'approved')->count(),
            'matches' => Interest::where('status', 'accepted')->count(),
        ]);

        $news = NewsItem::published()->where('show_on_landing', true)
            ->whereIn('type', ['news', 'poster'])->latest()->take(8)->get();

        $events = NewsItem::published()->where('type', 'event')
            ->where('event_at', '>=', now())->orderBy('event_at')->take(5)->get();

        $stories = SuccessStory::where('is_published', true)->latest()->take(6)->get();

        return view('landing.index', [
            'title' => 'अपनों के बीच जीवनसाथी की तलाश',
            'transparentNav' => true,
            'stats' => [
                'members' => $this->friendly($stats['members']),
                'biodata' => $this->friendly($stats['biodata']),
                'matches' => $this->friendly($stats['matches']),
            ],
            'news' => $news,
            'events' => $events,
            'stories' => $stories,
        ]);
    }

    /** "100+" style numbers so tiny counts still look healthy but honest. */
    private function friendly(int $n): string
    {
        if ($n < 10) return '10+';
        if ($n < 100) return (floor($n / 10) * 10).'+';
        if ($n < 1000) return (floor($n / 50) * 50).'+';
        return number_format(floor($n / 100) * 100).'+';
    }
}
