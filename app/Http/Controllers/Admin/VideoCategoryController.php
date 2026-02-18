<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoCategory;
use Illuminate\Http\Request;

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
        ]);

        VideoCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
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
        ]);

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
}
