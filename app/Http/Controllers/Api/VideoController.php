<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $query = Video::with('category')
            ->where('status', 'active')
            ->when($request->category_id, function ($q) use ($request) {
                $q->where('video_category_id', $request->category_id);
            });

        $videos = $query->latest()->paginate($perPage);

        $videos->getCollection()->transform(function ($video) {
            return [
                'id'          => $video->id,
                'title'       => $video->title,
                'status'      => $video->status,
                'video_url'   => asset('storage/' . $video->video_path),
                'category'    => $video->category?->name,
                'created_at'  => $video->created_at->toDateTimeString(),
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
}
