<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuccessStoryRequest;
use App\Models\AuditLog;
use App\Models\SuccessStory;
use App\Services\Admin\AdminScope;
use App\Services\Admin\ImageStore;

class StoryController extends Controller
{
    private function find(int|string $id): SuccessStory
    {
        return AdminScope::stories(SuccessStory::query())->findOrFail($id);
    }

    public function index()
    {
        $items = AdminScope::stories(SuccessStory::query())->latest()->paginate(15);

        return view('admin.stories.index', compact('items'));
    }

    public function create()
    {
        return view('admin.stories.form', ['item' => new SuccessStory(['is_published' => false])]);
    }

    public function store(SuccessStoryRequest $request)
    {
        $data = $request->safe()->except('image');
        if ($request->hasFile('image')) {
            $data['image'] = ImageStore::store($request->file('image'), 'stories');
        }
        $item = SuccessStory::create($data);
        AuditLog::record('story.create', 'story', $item->id, $item->couple_names);

        return redirect()->route('admin.stories.index')->with('status', __('Saved.'));
    }

    public function edit(int $id)
    {
        return view('admin.stories.form', ['item' => $this->find($id)]);
    }

    public function update(SuccessStoryRequest $request, int $id)
    {
        $item = $this->find($id);
        $data = $request->safe()->except('image');
        if ($request->hasFile('image')) {
            ImageStore::delete($item->image);
            $data['image'] = ImageStore::store($request->file('image'), 'stories');
        }
        $item->update($data);
        AuditLog::record('story.update', 'story', $item->id, $item->couple_names);

        return redirect()->route('admin.stories.index')->with('status', __('Saved.'));
    }

    public function destroy(int $id)
    {
        $item = $this->find($id);
        ImageStore::delete($item->image);
        AuditLog::record('story.delete', 'story', $item->id, $item->couple_names);
        $item->delete();

        return redirect()->route('admin.stories.index')->with('status', __('Deleted.'));
    }
}
