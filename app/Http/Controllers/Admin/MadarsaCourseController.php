<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MadarsaCourse;
use Illuminate\Http\Request;

class MadarsaCourseController extends Controller
{
    public function index()
    {
        $courses = MadarsaCourse::latest()->get();
        return view('admin.madarsa-courses', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MadarsaCourse::create($request->only('name', 'description'));

        return redirect()->route('madarsa-courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function update(Request $request, $id)
    {
        $course = MadarsaCourse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($request->only('name', 'description'));

        return redirect()->route('madarsa-courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy($id)
    {
        $course = MadarsaCourse::findOrFail($id);
        $course->delete();

        return redirect()->route('madarsa-courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function getCourses()
    {
        $course = MadarsaCourse::select('id','name')->get();
        
        return response([
            'success' => true,
            'data' => $course
        ]);
    }
}
