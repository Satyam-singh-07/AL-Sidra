<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OngoingWork;

class OngoingWorkApiController extends Controller
{
    public function index()
    {
        $works = OngoingWork::with(['images'])
            ->whereIn('status', ['in-progress', 'completed'])
            ->latest()
            ->get()
            ->map(function ($work) {
                return [
                    'id' => $work->id,
                    'title' => $work->title,
                    'description' => $work->description,
                    'image' => optional($work->images->first())->url
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $works
        ]);
    }

    public function show($id)
    {
        $work = OngoingWork::with(['images', 'videos'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $work->id,
                'title' => $work->title,
                'description' => $work->description,
                'address' => $work->address,
                'status' => $work->status,
                'created_at' => $work->created_at->toDateTimeString(),

                'images' => $work->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'url' => $img->url,
                    ];
                }),

                'videos' => $work->videos->map(function ($vid) {
                    return [
                        'id' => $vid->id,
                        'url' => $vid->url,
                    ];
                }),
            ]
        ]);
    }

}
