<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $user = Auth::guard('sanctum')->user();

        $query = Video::with('category')
            ->where('status', 'active');

        if ($user) {
            // Sort by seen: unseen (0) first, then seen (1)
            $query->leftJoin('video_views', function ($join) use ($user) {
                $join->on('videos.id', '=', 'video_views.video_id')
                    ->where('video_views.user_id', '=', $user->id);
            })
            ->select('videos.*')
            ->selectRaw('IF(video_views.id IS NULL, 0, 1) as is_seen')
            ->orderBy('is_seen', 'asc') // Unseen first (0 < 1)
            ->latest('videos.created_at');
        } else {
            $query->latest();
        }

        $videos = $query->paginate($perPage);

        $videos->getCollection()->transform(function ($video) {
            return [
                'id'         => $video->id,
                'title'      => $video->title,
                'status'     => $video->status,
                'video_url'  => asset('storage/' . $video->video_path),
                'is_seen'    => isset($video->is_seen) ? (bool)$video->is_seen : false,
                'category'   => [
                    'id'   => $video->video_category_id,
                    'name' => $video->category?->name,
                    'image_url' => $video->category?->image_url
                ],
                'created_at' => $video->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $videos->items(),
            'meta'   => [
                'current_page' => $videos->currentPage(),
                'last_page'    => $videos->lastPage(),
                'per_page'     => $videos->perPage(),
                'total'        => $videos->total(),
            ]
        ]);
    }

    public function markAsSeen(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $video = Video::find($id);
        if (!$video) {
            return response()->json([
                'status' => 'error',
                'message' => 'Video not found'
            ], 404);
        }

        VideoView::updateOrCreate([
            'user_id' => $user->id,
            'video_id' => $id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Video marked as seen'
        ]);
    }
}
