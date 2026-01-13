<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotTopic;
use Illuminate\Http\Request;

class HotTopicController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $topics = HotTopic::query()
            ->where('status', 'published')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $topics->through(function ($topic) {
                return [
                    'id'          => $topic->id,
                    'title'       => $topic->title,
                    'description' => $topic->description,
                    'image_url'   => $topic->image_url,
                    'video_url'   => $topic->video_url,
                    'is_trending' => $topic->is_trending,
                    'views'       => $topic->views,
                    'created_at'  => $topic->created_at->toDateTimeString(),
                ];
            }),
            'meta' => [
                'current_page' => $topics->currentPage(),
                'per_page'     => $topics->perPage(),
                'total'        => $topics->total(),
                'last_page'    => $topics->lastPage(),
                'has_more'     => $topics->hasMorePages(),
            ],
        ]);

    }
}
