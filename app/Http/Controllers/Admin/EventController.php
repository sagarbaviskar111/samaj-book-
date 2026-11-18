<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\AuditLog;
use App\Models\Event;
use App\Services\Admin\AdminNotifier;
use App\Services\Admin\AdminScope;
use App\Services\Admin\ImageStore;
use App\Support\BiodataOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    private function find(int|string $id): Event
    {
        return AdminScope::events(Event::query())->findOrFail($id);
    }

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'past' ? 'past' : 'upcoming';
        $q = AdminScope::events(Event::query())->withCount(['registrations', 'registrations as joined_count' => fn ($r) => $r->whereNotNull('joined_at')]);
        $events = ($tab === 'past' ? $q->past()->orderByDesc('starts_at') : $q->upcoming()->orderBy('starts_at'))
            ->paginate(15)->withQueryString();

        return view('admin.events.index', compact('events', 'tab'));
    }

    public function create()
    {
        return view('admin.events.form', [
            'event' => new Event(['is_published' => true, 'registration_open' => true, 'popup_enabled' => true, 'reminder_interval_min' => 15]),
            'districts' => array_keys(BiodataOptions::districts()),
        ]);
    }

    private function payload(EventRequest $request): array
    {
        $data = $request->safe()->except(['image', 'remove_image', 'notify_all']);
        if ($area = AdminScope::area()) {
            $data['area'] = $area; // sub-admin events are always tied to their area
        }

        return $data;
    }

    public function store(EventRequest $request)
    {
        $data = $this->payload($request);
        if ($request->hasFile('image')) {
            $data['image'] = ImageStore::store($request->file('image'), 'events');
        }
        $data['created_by'] = auth()->id();
        $event = Event::create($data);
        AuditLog::record('event.create', 'event', $event->id, $event->title);

        $sent = $event->is_published && $request->boolean('notify_all') ? $this->notifyAll($event) : 0;

        return redirect()->route('admin.events.index')->with('status', __('Saved.').($sent ? ' '.__('Notified :n members.', ['n' => $sent]) : ''));
    }

    public function edit(int $id)
    {
        return view('admin.events.form', ['event' => $this->find($id), 'districts' => array_keys(BiodataOptions::districts())]);
    }

    public function update(EventRequest $request, int $id)
    {
        $event = $this->find($id);
        $wasPublished = $event->is_published;
        $data = $this->payload($request);

        if ($request->hasFile('image')) {
            ImageStore::delete($event->image);
            $data['image'] = ImageStore::store($request->file('image'), 'events');
        } elseif ($request->boolean('remove_image')) {
            ImageStore::delete($event->image);
            $data['image'] = null;
        }
        $event->update($data);
        AuditLog::record('event.update', 'event', $event->id, $event->title);

        $sent = ! $wasPublished && $event->is_published && $request->boolean('notify_all') ? $this->notifyAll($event) : 0;

        return redirect()->route('admin.events.index')->with('status', __('Saved.').($sent ? ' '.__('Notified :n members.', ['n' => $sent]) : ''));
    }

    public function destroy(int $id)
    {
        $event = $this->find($id);
        ImageStore::delete($event->image);
        AuditLog::record('event.delete', 'event', $event->id, $event->title);
        $event->delete();

        return redirect()->route('admin.events.index')->with('status', __('Deleted.'));
    }

    public function registrants(int $id)
    {
        $event = $this->find($id);
        $regs = $event->registrations()->with('user:id,name,phone,city,district')->latest()->paginate(30);

        return view('admin.events.registrants', compact('event', 'regs'));
    }

    public function registrantsCsv(int $id)
    {
        $event = $this->find($id);
        $regs = $event->registrations()->with('user:id,name,phone,city,district')->oldest()->get();
        // guard against spreadsheet formula injection
        $safe = fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'".$v : (string) $v;

        return response()->streamDownload(function () use ($regs, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Name', 'Phone', 'Area', 'Registered at', 'Joined']);
            foreach ($regs as $r) {
                fputcsv($out, [
                    $safe($r->user?->name), $safe($r->user?->phone), $safe($r->user?->district ?: $r->user?->city),
                    $r->created_at?->format('Y-m-d H:i'), $r->joined_at ? $r->joined_at->format('Y-m-d H:i') : 'No',
                ]);
            }
            fclose($out);
        }, 'event-'.$event->id.'-'.Str::slug($event->title).'-registrants.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    private function notifyAll(Event $event): int
    {
        $body = trim($event->startsHuman().($event->place ? ' · '.$event->place : ''));

        return AdminNotifier::broadcast('event', $event->title, $body, AdminNotifier::routeOrNull('events.show', $event), $event->area);
    }
}
