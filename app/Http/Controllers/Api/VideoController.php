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

    $videos = Video::where('status', 'active')
        ->latest()
        ->paginate($perPage);

    $videos->getCollection()->transform(function ($video) {
        return [
            'id'         => $video->id,
            'title'      => $video->title,
            'status'     => $video->status,
            'video_url'  => asset('storage/' . $video->video_path),
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

}
