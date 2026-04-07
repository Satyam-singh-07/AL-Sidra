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

        $cleanDescription = $this->sanitizeHtml($request->description);

        RuhaniIjalCategory::create([
            'name' => $request->name,
            'description' => $cleanDescription,
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

        $cleanDescription = $this->sanitizeHtml($request->description);

        $category->update([
            'name' => $request->name,
            'description' => $cleanDescription,
        ]);

        return redirect()
            ->route('ruhani-ijal-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Helper to sanitize dirty HTML from pastes
     */
    private function sanitizeHtml($html)
    {
        if (empty($html)) return $html;

        // 1. Strip all tags except basic formatting
        $allowedTags = '<p><br><b><i><u><ul><ol><li><h3><h4><h5><h6><a><blockquote>';
        $stripped = strip_tags($html, $allowedTags);

        // 2. Remove problematic attributes like _ngcontent, data-*, aria-*, etc.
        // This preserves the tags but cleans the garbage inside them
        $clean = preg_replace('/\s(_ngcontent|data-|inline-copy-host|aria-|id|style|class|inline-copy-button)[^=>]*="[^"]*"/i', '', $stripped);
        $clean = preg_replace('/\s(_ngcontent|data-|inline-copy-host|aria-|id|style|class|inline-copy-button)[^=>]*=\'[^\']*\'/i', '', $clean);

        return trim($clean);
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
