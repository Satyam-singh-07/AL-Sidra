<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuhaniIjalCategory;
use Illuminate\Http\Request;

class RuhaniIjalCategoryController extends Controller
{
    /**
     * Display all ruhani ijal categories
     */
    public function index()
    {
        $categories = RuhaniIjalCategory::latest()->get();

        return view('admin.ruhani-ijal-categories', compact('categories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        RuhaniIjalCategory::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('ruhani-ijal-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        $category = RuhaniIjalCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('ruhani-ijal-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        $category = RuhaniIjalCategory::findOrFail($id);

        $category->delete();

        return redirect()
            ->route('ruhani-ijal-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
