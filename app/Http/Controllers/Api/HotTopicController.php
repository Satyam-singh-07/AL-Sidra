<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotTopic;
use App\Models\HotTopicView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotTopicController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $user = Auth::guard('sanctum')->user();

        $query = HotTopic::query()
            ->where('status', 'published');

        if ($user) {
            $query->leftJoin('hot_topic_views', function ($join) use ($user) {
                $join->on('hot_topics.id', '=', 'hot_topic_views.hot_topic_id')
                    ->where('hot_topic_views.user_id', '=', $user->id);
            })
                ->select('hot_topics.*')
                ->selectRaw('IF(hot_topic_views.id IS NULL, 0, 1) as is_read');
        } else {
            $query->select('*')->selectRaw('0 as is_read');
        }

        $topics = $query->latest('hot_topics.created_at')
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
                    'is_read'     => (bool)$topic->is_read,
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

    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $topic = HotTopic::findOrFail($id);

        $alreadyRead = HotTopicView::where('user_id', $user->id)
            ->where('hot_topic_id', $topic->id)
            ->exists();

        if (!$alreadyRead) {
            HotTopicView::create([
                'user_id' => $user->id,
                'hot_topic_id' => $topic->id
            ]);

            $topic->increment('views');
        }

        return response()->json([
            'success' => true,
            'message' => 'Topic marked as read'
        ]);
    }
}
