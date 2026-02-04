<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotTopic;
use App\Models\TopicUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TopicUpdateController extends Controller
{
    /**
     * Display a listing of updates for a topic.
     */
    public function index(HotTopic $hotTopic)
    {
        $updates = $hotTopic->updates()
            ->latest()
            ->paginate(10);

        return view('admin.topic-updates', compact('hotTopic', 'updates'));
    }

    public function store(Request $request, HotTopic $hotTopic)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'content' => 'required|string',
            'image_path' => 'nullable|string',
            'video_path' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request
                ->file('image')
                ->store('topic-updates/images', 'public');
        }

        if ($request->hasFile('video')) {
            $validated['video_path'] = $request
                ->file('video')
                ->store('topic-updates/videos', 'public');
        }

        $hotTopic->updates()->create($validated);

        return redirect()
            ->route('hot-topics.topic-updates.index', $hotTopic->id)
            ->with('success', 'Topic update added successfully.');
    }

    public function update(Request $request, HotTopic $hotTopic, TopicUpdate $topicUpdate)
    {
        if ($topicUpdate->hot_topic_id !== $hotTopic->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'content' => 'required|string',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi,webm|max:20480',
        ]);

        if ($request->hasFile('image')) {

            if ($topicUpdate->image_path && Storage::disk('public')->exists($topicUpdate->image_path)) {
                Storage::disk('public')->delete($topicUpdate->image_path);
            }

            $validated['image_path'] = $request
                ->file('image')
                ->store('topic-updates/images', 'public');
        }

        if ($request->hasFile('video')) {

            if ($topicUpdate->video_path && Storage::disk('public')->exists($topicUpdate->video_path)) {
                Storage::disk('public')->delete($topicUpdate->video_path);
            }

            $validated['video_path'] = $request
                ->file('video')
                ->store('topic-updates/videos', 'public');
        }

        $topicUpdate->update($validated);

        return redirect()
            ->route('hot-topics.topic-updates.index', $hotTopic->id)
            ->with('success', 'Topic update updated successfully.');
    }

    public function destroy(HotTopic $hotTopic, TopicUpdate $topicUpdate)
    {
        if ($topicUpdate->hot_topic_id !== $hotTopic->id) {
            abort(404);
        }

        if ($topicUpdate->image_path && Storage::disk('public')->exists($topicUpdate->image_path)) {
            Storage::disk('public')->delete($topicUpdate->image_path);
        }

        if ($topicUpdate->video_path && Storage::disk('public')->exists($topicUpdate->video_path)) {
            Storage::disk('public')->delete($topicUpdate->video_path);
        }

        $topicUpdate->delete();

        return redirect()
            ->route('hot-topics.topic-updates.index', $hotTopic->id)
            ->with('success', 'Topic update deleted successfully.');
    }
}
