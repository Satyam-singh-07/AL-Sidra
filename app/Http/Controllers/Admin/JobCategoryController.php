<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class JobCategoryController extends Controller
{
    /**
     * Display all job categories
     */
    public function index()
    {
        $categories = JobCategory::latest()->get();
        return view('admin.job-categories.index', compact('categories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:masjid,madarsa',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        JobCategory::create([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('job-categories.index')
            ->with('success', 'Job Category created successfully.');
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = JobCategory::findOrFail($id);

        $request->validate([
            'type' => 'required|in:masjid,madarsa',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('job-categories.index')
            ->with('success', 'Job Category updated successfully.');
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $category = JobCategory::findOrFail($id);
        $category->delete();

        return redirect()
            ->route('job-categories.index')
            ->with('success', 'Job Category deleted successfully.');
    }
}
