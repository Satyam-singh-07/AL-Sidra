<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::with('category')->latest()->get();
        $categories = \App\Models\VideoCategory::where('status', 'active')->get();

        return view('admin.videos', compact('videos', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|mimes:mp4,webm,mov|max:51200',
            'status' => 'required|in:active,inactive',
            'video_category_id' => 'nullable|exists:video_categories,id',
        ]);

        $path = $request->file('video')->store('videos', 'public');

        Video::create([
            'title' => $request->title,
            'video_path' => $path,
            'status' => $request->status,
            'video_category_id' => $request->video_category_id,
        ]);

        return redirect()->route('videos.index')->with('success', 'Video added');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'nullable|mimes:mp4,webm,mov',
            'status' => 'required|in:active,inactive',
            'video_category_id' => 'nullable|exists:video_categories,id',
        ]);

        if ($request->hasFile('video')) {
            Storage::disk('public')->delete($video->video_path);
            $video->video_path = $request->file('video')->store('videos', 'public');
        }

        $video->update([
            'title' => $request->title,
            'status' => $request->status,
            'video_category_id' => $request->video_category_id,
        ]);

        return back()->with('success', 'Video updated');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        Storage::disk('public')->delete($video->video_path);
        $video->delete();

        return back()->with('success', 'Video deleted');
    }
}
