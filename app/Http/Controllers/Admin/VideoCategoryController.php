<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoCategoryController extends Controller
{
    /**
     * Display all video categories
     */
    public function index()
    {
        $videoCategories = VideoCategory::latest()->get();

        return view('admin.video_categories', compact('videoCategories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('video_categories', 'public');
        }

        VideoCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'image' => $imagePath,

        ]);

        return redirect()
            ->route('video-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = VideoCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $imagePath = $request->file('image')->store('video_categories', 'public');
            $category->image = $imagePath;
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('video-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $category = VideoCategory::findOrFail($id);

        $category->delete();

        return redirect()
            ->route('video-categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus($id)
    {
        $category = VideoCategory::findOrFail($id);

        $category->status = $category->status === 'active'
            ? 'inactive'
            : 'active';

        $category->save();

        return redirect()->route('video-categories.index');
    }

    public function show(Request $request, $id)
    {
        $perPage = $request->get('per_page', 10);

        $category = VideoCategory::where('status', 'active')
            ->findOrFail($id);

        $videos = $category->videos()
            ->where('status', 'active')
            ->latest()
            ->paginate($perPage);

        $videoData = $videos->getCollection()->transform(function ($video) {
            return [
                'id'        => $video->id,
                'title'     => $video->title,
                'description' => $video->description,
                'video_url' => asset('storage/' . $video->video_path),
                'created_at' => $video->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'category' => [
                'id'    => $category->id,
                'name'  => $category->name,
                'image' => $category->image
                    ? asset('storage/' . $category->image)
                    : null,
            ],
            'videos' => $videoData,
            'meta'   => [
                'current_page' => $videos->currentPage(),
                'last_page'    => $videos->lastPage(),
                'per_page'     => $videos->perPage(),
                'total'        => $videos->total(),
            ]
        ]);
    }
}
