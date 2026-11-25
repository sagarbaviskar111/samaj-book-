<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Services\NotificationPrefs;
use App\Services\PushService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $prefs = NotificationPrefs::for($user);

        $items = $user->notifications()->latest()->paginate(30);
        $items->setCollection($items->getCollection()->filter(
            fn ($n) => $prefs[NotificationPrefs::groupOf($n->data['type'] ?? null)] ?? true
        )->values());

        $today = $items->filter(fn ($n) => $n->created_at->isToday());
        $earlier = $items->reject(fn ($n) => $n->created_at->isToday());

        return view('notifications.index', [
            'items' => $items,
            'today' => $today,
            'earlier' => $earlier,
            'unread' => $user->unreadNotifications()->count(),
            'pushReady' => PushService::configured(),
            'vapidPublic' => env('VAPID_PUBLIC_KEY'),
        ]);
    }

    public function read(Request $request, string $id)
    {
        $n = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $n->markAsRead();
        $url = $n->data['url'] ?? null;

        return redirect($this->safeUrl($url) ?? route('notifications.index'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')->with('status', __('All notifications marked as read.'));
    }

    public function settings(Request $request)
    {
        return view('notifications.settings', [
            'prefs' => NotificationPrefs::for($request->user()),
            'labels' => array_map(fn ($l) => __($l), NotificationPrefs::GROUPS),
            'pushReady' => PushService::configured(),
            'vapidPublic' => env('VAPID_PUBLIC_KEY'),
        ]);
    }

    public function saveSettings(Request $request)
    {
        NotificationPrefs::save($request->user(), (array) $request->input('prefs', []));

        return redirect()->route('notifications.settings')->with('status', __('Notification settings saved.'));
    }

    /** Only allow same-host redirects. */
    private function safeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if (str_starts_with($url, '/')) {
            return $url;
        }
        $host = parse_url($url, PHP_URL_HOST);

        return $host === request()->getHost() ? $url : null;
    }
}
