<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    public static function popupKey(int $userId): string
    {
        return 'events.popup.'.$userId;
    }

    private function visible(Request $request)
    {
        return Event::published()->forAudience($request->user());
    }

    private function findVisible(Request $request, Event $event): Event
    {
        abort_unless($event->is_published && Event::forAudience($request->user())->whereKey($event->id)->exists(), 404);

        return $event;
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['mine', 'past'], true) ? $request->query('tab') : 'upcoming';
        $uid = $request->user()->id;
        $q = $this->visible($request)->withCount('registrations');

        $q = match ($tab) {
            'mine' => $q->whereHas('registrations', fn ($r) => $r->where('user_id', $uid))->orderByDesc('starts_at'),
            'past' => $q->past()->orderByDesc('starts_at'),
            default => $q->upcoming()->orderBy('starts_at'),
        };
        $events = $q->paginate(10)->withQueryString();
        $registeredIds = EventRegistration::where('user_id', $uid)->whereIn('event_id', $events->pluck('id'))->pluck('event_id')->all();

        return view('events.index', compact('events', 'tab', 'registeredIds'));
    }

    public function show(Request $request, Event $event)
    {
        $this->findVisible($request, $event);
        $reg = EventRegistration::where('event_id', $event->id)->where('user_id', $request->user()->id)->first();
        $count = $event->registrations()->count();

        return view('events.show', compact('event', 'reg', 'count'));
    }

    public function register(Request $request, Event $event)
    {
        $this->findVisible($request, $event);
        $ok = $event->acceptsRegistration();
        if ($ok) {
            EventRegistration::firstOrCreate(['event_id' => $event->id, 'user_id' => $request->user()->id]);
            Cache::forget(self::popupKey($request->user()->id));
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $ok ? __('Registered!') : __('Registration is closed.')], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'status' : 'error', $ok ? __('You are registered.') : __('Registration is closed.'));
    }

    public function unregister(Request $request, Event $event)
    {
        $this->findVisible($request, $event);
        EventRegistration::where('event_id', $event->id)->where('user_id', $request->user()->id)->delete();
        Cache::forget(self::popupKey($request->user()->id));

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', __('Registration cancelled.'));
    }

    public function join(Request $request, Event $event)
    {
        $this->findVisible($request, $event);
        $reg = EventRegistration::where('event_id', $event->id)->where('user_id', $request->user()->id)->first();
        if (! $reg) {
            return redirect()->route('events.show', $event)->with('error', __('Please register first.'));
        }
        if (! $reg->joined_at) {
            $reg->update(['joined_at' => now()]);
        }
        Cache::forget(self::popupKey($request->user()->id));

        return $event->join_url ? redirect()->away($event->join_url) : redirect()->route('events.show', $event);
    }

    public function ics(Request $request, Event $event)
    {
        $this->findVisible($request, $event);
        $fmt = fn ($d) => $d->copy()->utc()->format('Ymd\THis\Z');
        $esc = fn ($s) => str_replace(["\\", ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], (string) $s);
        $body = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//SamajBook//Events//EN', 'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@samajbook', 'DTSTAMP:'.$fmt(now()),
            'DTSTART:'.$fmt($event->starts_at), 'DTEND:'.$fmt($event->endsAtEffective()),
            'SUMMARY:'.$esc($event->title), 'LOCATION:'.$esc($event->place), 'DESCRIPTION:'.$esc($event->description),
            'END:VEVENT', 'END:VCALENDAR',
        ])."\r\n";

        return response($body, 200, ['Content-Type' => 'text/calendar; charset=utf-8', 'Content-Disposition' => 'attachment; filename="event-'.$event->id.'.ics"']);
    }

    /** Cheap JSON for the global popup. Cached 30s per user. */
    public function popup(Request $request)
    {
        $user = $request->user();
        $data = Cache::remember(self::popupKey($user->id), 30, function () use ($user) {
            $register = null;
            $join = null;
            if ($user->role === 'user') {
                $base = Event::published()->forAudience($user)->where('popup_enabled', true)->upcoming();
                $regIds = EventRegistration::where('user_id', $user->id);

                $r = (clone $base)->where('registration_open', true)
                    ->whereNotIn('id', (clone $regIds)->select('event_id'))->orderBy('starts_at')->first();
                if ($r) {
                    $register = [
                        'id' => $r->id, 'title' => $r->title, 'starts_at_human' => $r->startsHuman(), 'place' => $r->place,
                        'image_url' => $r->imageUrl(), 'register_url' => route('events.register', $r), 'event_url' => route('events.show', $r),
                    ];
                }

                $j = (clone $base)->today()
                    ->whereIn('id', (clone $regIds)->whereNull('joined_at')->select('event_id'))->orderBy('starts_at')->first();
                if ($j) {
                    $join = [
                        'id' => $j->id, 'title' => $j->title, 'starts_at_human' => $j->startsHuman(), 'place' => $j->place,
                        'image_url' => $j->imageUrl(), 'join_url' => route('events.join', $j),
                        'interval_min' => (int) $j->reminder_interval_min, 'event_url' => route('events.show', $j),
                    ];
                }
            }

            return ['register' => $register, 'join' => $join];
        });

        return response()->json($data + ['server_time' => now()->toIso8601String()]);
    }
}
