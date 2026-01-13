<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotTopic;
use App\Models\TopicUpdate;
use Illuminate\Http\Request;

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

    /**
     * Store a newly created update.
     */
    public function store(Request $request, HotTopic $hotTopic)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'content' => 'required|string',
        ]);

        $hotTopic->updates()->create($validated);

        return redirect()
            ->route('hot-topics.topic-updates.index', $hotTopic->id)
            ->with('success', 'Topic update added successfully.');
    }

    /**
     * Update the specified update.
     */
    public function update(Request $request, HotTopic $hotTopic, TopicUpdate $topicUpdate)
    {
        // Safety check (VERY IMPORTANT)
        if ($topicUpdate->hot_topic_id !== $hotTopic->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'content' => 'required|string',
        ]);

        $topicUpdate->update($validated);

        return redirect()
            ->route('hot-topics.topic-updates.index', $hotTopic->id)
            ->with('success', 'Topic update updated successfully.');
    }

    /**
     * Remove the specified update.
     */
    public function destroy(HotTopic $hotTopic, TopicUpdate $topicUpdate)
    {
        // Safety check
        if ($topicUpdate->hot_topic_id !== $hotTopic->id) {
            abort(404);
        }

        $topicUpdate->delete();

        return redirect()
            ->route('hot-topics.topic-updates.index', $hotTopic->id)
            ->with('success', 'Topic update deleted successfully.');
    }
}
