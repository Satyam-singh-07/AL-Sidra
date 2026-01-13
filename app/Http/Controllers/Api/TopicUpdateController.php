<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotTopic;
use Illuminate\Http\Request;

class TopicUpdateController extends Controller
{
    public function index(Request $request, $hotTopicId)
    {
        $perPage = (int) $request->get('per_page', 10);

        $hotTopic = HotTopic::where('status', 'published')
            ->findOrFail($hotTopicId);

        $updates = $hotTopic->updates()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'topic' => [
                'id'        => $hotTopic->id,
                'title'     => $hotTopic->title,
                'image_url' => $hotTopic->image_url,
            ],
            'data' => $updates->through(fn ($update) => [
                'id'         => $update->id,
                'title'      => $update->title,
                'content'    => $update->content,
                'created_at' => $update->created_at->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $updates->currentPage(),
                'per_page'     => $updates->perPage(),
                'total'        => $updates->total(),
                'last_page'    => $updates->lastPage(),
                'has_more'     => $updates->hasMorePages(),
            ],
        ]);
    }
}
