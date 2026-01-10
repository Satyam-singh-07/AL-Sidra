<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index()
    {
        $communities = Community::latest()->get();
        return view('admin.communities', compact('communities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Community::create($request->only('name', 'description', 'status'));

        return redirect()->back()->with('success', 'Community added successfully');
    }

    public function update(Request $request, Community $community)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $community->update($request->only('name', 'description', 'status'));

        return redirect()->back()->with('success', 'Community updated successfully');
    }

    public function destroy(Community $community)
    {
        $community->delete();

        return redirect()->back()->with('success', 'Community deleted successfully');
    }

    public function toggleStatus(Community $community)
    {
        $community->update([
            'status' => $community->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->back()->with('success','Status updated successfully');
    }

}

