<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TopicUpdate;


class HotTopicController extends Controller
{
    /**
     * Display a listing of the topics.
     */

    public function index(Request $request)
{
    $query = HotTopic::query()->withCount('updates');

    // 🔍 Search
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }

    // 🔃 Sorting
    switch ($request->sort) {
        case 'popular':
            $query->orderByDesc('views');
            break;

        case 'updates':
            $query->orderByDesc('updates_count');
            break;

        case 'oldest':
            $query->oldest();
            break;

        default:
            $query->latest();
            break;
    }

    $topics = $query->paginate(50);

    // 📊 Stats
    $totalTopics  = HotTopic::count();
    $activeTopics = HotTopic::where('status', 'published')->count();
    $totalUpdates = TopicUpdate::count();
    $todayViews   = HotTopic::whereDate('updated_at', today())->sum('views');

    return view('admin.hot-topics', compact(
        'topics',
        'totalTopics',
        'activeTopics',
        'totalUpdates',
        'todayViews'
    ));
}


    /**
     * Show the form for creating a new topic.
     */
    public function create()
    {
        return view('admin.hot-topics-create');
    }

    /**
     * Store a newly created topic in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'required|string',
            'image'       => 'nullable|image|max:2048',
            'video'       => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:20480',
            'action'      => 'required|in:draft,publish',
        ]);

        $imagePath = null;
        $videoPath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('hot-topics/images', 'public');
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('hot-topics/videos', 'public');
        }

        HotTopic::create([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'image'       => $imagePath,
            'video'       => $videoPath,
            'status'      => $validated['action'] === 'publish' ? 'published' : 'draft',
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('hot-topics.index')
            ->with('success', 'Hot topic created successfully.');
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(HotTopic $hotTopic)
    {
        return view('admin.hot-topics-edit', compact('hotTopic'));
    }

    /**
     * Update the specified topic in storage.
     */
    public function update(Request $request, HotTopic $hotTopic)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'required|string',
            'image'       => 'nullable|image|max:2048',
            'video'       => 'nullable|mimetypes:video/mp4,video/webm,video/ogg|max:20480',
            'status'      => 'nullable|in:draft,published',
        ]);

        if ($request->hasFile('image')) {
            if ($hotTopic->image) {
                Storage::disk('public')->delete($hotTopic->image);
            }
            $validated['image'] = $request->file('image')->store('hot-topics/images', 'public');
        }

        if ($request->hasFile('video')) {
            if ($hotTopic->video) {
                Storage::disk('public')->delete($hotTopic->video);
            }
            $validated['video'] = $request->file('video')->store('hot-topics/videos', 'public');
        }

        $hotTopic->update($validated);

        return redirect()
            ->route('hot-topics.index')
            ->with('success', 'Hot topic updated successfully.');
    }

    /**
     * Remove the specified topic from storage.
     */
    public function destroy(HotTopic $hotTopic)
    {
        if ($hotTopic->image) {
            Storage::disk('public')->delete($hotTopic->image);
        }

        if ($hotTopic->video) {
            Storage::disk('public')->delete($hotTopic->video);
        }

        $hotTopic->delete();

        return redirect()
            ->route('hot-topics.index')
            ->with('success', 'Hot topic deleted successfully.');
    }

    public function toggleStatus(HotTopic $hotTopic)
    {
        $hotTopic->update([
            'status' => $hotTopic->status === 'published' ? 'draft' : 'published',
        ]);

        return back()->with('success', 'Topic status updated.');
    }

}
