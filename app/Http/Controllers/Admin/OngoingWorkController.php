<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OngoingWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OngoingWorkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $works = OngoingWork::with(['images', 'videos'])
            ->latest()
            ->paginate(20);

        return view('admin.ongoing-works', compact('works'));
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
            'status' => 'required',
            'description' => 'required',
            'address' => 'required',
            'images' => 'required|array|min:3',
            'images.*' => 'image|max:10240',
            'videos' => 'required|array|min:2',
            'videos.*' => 'mimetypes:video/mp4,video/quicktime|max:51200',
        ]);

        $work = OngoingWork::create($request->only(
            'title',
            'status',
            'description',
            'address'
        ));

        foreach ($request->images as $image) {
            $path = $image->store('ongoing-works/images', 'public');
            $work->images()->create(['path' => $path]);
        }

        foreach ($request->videos as $video) {
            $path = $video->store('ongoing-works/videos', 'public');
            $work->videos()->create(['path' => $path]);
        }

        return redirect()->back()->with('success', 'Work added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $work = OngoingWork::with(['images', 'videos'])->findOrFail($id);

        return response()->json($work);
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
        $work = OngoingWork::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required',
            'description' => 'required',
            'address' => 'required',

            'images' => 'nullable|array',
            'images.*' => 'image|max:10240',

            'videos' => 'nullable|array',
            'videos.*' => 'mimetypes:video/mp4,video/quicktime|max:51200',
        ]);

        // Update text fields
        $work->update($request->only('title', 'status', 'description', 'address'));

        // Add New Images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store("ongoing-works/images", "public");
                $work->images()->create(['path' => $path]);
            }
        }

        // Add New Videos
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store("ongoing-works/videos", "public");
                $work->videos()->create(['path' => $path]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Work updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $work = OngoingWork::with(['images', 'videos'])->findOrFail($id);

        foreach ($work->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete(); // delete row
        }

        foreach ($work->videos as $vid) {
            Storage::disk('public')->delete($vid->path);
            $vid->delete(); // delete row
        }

        $work->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work deleted successfully'
        ]);
    }
}
