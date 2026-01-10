<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberCategory;
use Illuminate\Http\Request;

class MemberCategoryController extends Controller
{
    public function index()
    {
        $memberCategories = MemberCategory::latest()->get();
        return view('admin.member-categories', compact('memberCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:member_categories,name',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string'
        ]);

        MemberCategory::create($request->all());

        return back()->with('success', 'Category created');
    }

    public function update(Request $request, MemberCategory $membercategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:member_categories,name,' . $membercategory->id,
            'status' => 'required|in:active,inactive,pending',
            'description' => 'nullable|string'
        ]);

        $membercategory->update($request->all());

        return back()->with('success', 'Category updated');
    }

    public function destroy(MemberCategory $membercategory)
    {
        $membercategory->delete();
        return back()->with('success', 'Category deleted');
    }

    public function toggleStatus(MemberCategory $membercategory)
    {
        $membercategory->update([
            'status' => $membercategory->status === 'active' ? 'inactive' : 'active'
        ]);

        return back()->with('success', 'Status updated');
    }

}

