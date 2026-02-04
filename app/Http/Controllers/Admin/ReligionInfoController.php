<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReligionInfo;

class ReligionInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $query = ReligionInfo::query();

        if ($request->category && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        $infos = $query->latest()->paginate(30);

        return view('admin.religioninfo', compact('infos'));
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
        $data = $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'content' => 'required',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('religion', 'public');
        }

        ReligionInfo::create($data);

        return redirect()->route('religion-info.index')
            ->with('success', 'Information added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return response()->json(
            ReligionInfo::findOrFail($id)
        );
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return response()->json(
            ReligionInfo::findOrFail($id)
        );
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $info = ReligionInfo::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'content' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        $info->update($data);

        return redirect()
            ->route('religion-info.index')
            ->with('success', 'Information updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $info = ReligionInfo::findOrFail($id);

        $info->delete();

        return redirect()
            ->route('religion-info.index')
            ->with('success', 'Information deleted successfully');
    }
}
