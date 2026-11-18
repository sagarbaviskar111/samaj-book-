<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsItemRequest;
use App\Models\AuditLog;
use App\Models\NewsItem;
use App\Services\Admin\AdminNotifier;
use App\Services\Admin\AdminScope;
use App\Services\Admin\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    private function find(int|string $id): NewsItem
    {
        return AdminScope::news(NewsItem::query())->findOrFail($id);
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $items = AdminScope::news(NewsItem::query())
            ->when(in_array($type, ['news', 'poster', 'event'], true), fn ($q) => $q->where('type', $type))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.news.index', compact('items', 'type'));
    }

    public function create()
    {
        return view('admin.news.form', ['item' => new NewsItem(['type' => 'news', 'is_published' => true, 'show_on_landing' => true])]);
    }

    public function store(NewsItemRequest $request)
    {
        $data = $request->safe()->except(['image', 'remove_image', 'notify_all']);
        if ($request->hasFile('image')) {
            $data['image'] = ImageStore::store($request->file('image'), 'news');
        }
        $data['created_by'] = auth()->id();
        if ($area = AdminScope::area()) {
            $data['area'] = $area; // sub-admin content is always tied to their area
        }
        $item = NewsItem::create($data);
        AuditLog::record('news.create', 'news', $item->id, $item->title);

        $sent = 0;
        if ($item->is_published && $request->boolean('notify_all')) {
            $sent = $this->notifyAll($item);
        }

        return redirect()->route('admin.news.index')->with('status', __('Saved.').($sent ? ' '.__('Notified :n members.', ['n' => $sent]) : ''));
    }

    public function edit(int $id)
    {
        return view('admin.news.form', ['item' => $this->find($id)]);
    }

    public function update(NewsItemRequest $request, int $id)
    {
        $item = $this->find($id);
        $wasPublished = $item->is_published;
        $data = $request->safe()->except(['image', 'remove_image', 'notify_all']);

        if ($request->hasFile('image')) {
            ImageStore::delete($item->image);
            $data['image'] = ImageStore::store($request->file('image'), 'news');
        } elseif ($request->boolean('remove_image')) {
            ImageStore::delete($item->image);
            $data['image'] = null;
        }
        $item->update($data);
        AuditLog::record('news.update', 'news', $item->id, $item->title);

        $sent = 0;
        if (! $wasPublished && $item->is_published && $request->boolean('notify_all')) {
            $sent = $this->notifyAll($item);
        }

        return redirect()->route('admin.news.index')->with('status', __('Saved.').($sent ? ' '.__('Notified :n members.', ['n' => $sent]) : ''));
    }

    public function togglePublish(int $id)
    {
        $item = $this->find($id);
        $item->update(['is_published' => ! $item->is_published]);
        AuditLog::record($item->is_published ? 'news.publish' : 'news.unpublish', 'news', $item->id, $item->title);

        return back()->with('status', $item->is_published ? __('Published.') : __('Unpublished.'));
    }

    public function destroy(int $id)
    {
        $item = $this->find($id);
        ImageStore::delete($item->image);
        AuditLog::record('news.delete', 'news', $item->id, $item->title);
        $item->delete();

        return redirect()->route('admin.news.index')->with('status', __('Deleted.'));
    }

    private function notifyAll(NewsItem $item): int
    {
        $isEvent = $item->type === 'event';
        $body = $isEvent
            ? trim(($item->event_at?->format('d M, h:i A') ?? '').' '.($item->event_place ? '· '.$item->event_place : ''))
            : Str::limit(strip_tags((string) $item->body), 120);

        // sub-admin: only active members of their area; main admin: everyone
        return AdminNotifier::broadcast($isEvent ? 'event' : 'news', $item->title, $body ?: null, AdminNotifier::routeOrNull('home'), AdminScope::area());
    }
}
