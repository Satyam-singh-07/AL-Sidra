<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MuqquirProfile;
use Illuminate\Http\Request;

class MuqquirController extends Controller
{
    /**
     * Display a listing of Muqquir registrations.
     */
    public function index()
    {
        $muqquirs = MuqquirProfile::with(['user', 'videos'])->latest()->get();
        return view('admin.muqquirs', compact('muqquirs'));
    }

    /**
     * Display the specified Muqquir registration details.
     */
    public function show($id)
    {
        $muqquir = MuqquirProfile::with(['user', 'videos'])->findOrFail($id);
        return view('admin.muqquir-show', compact('muqquir'));
    }

    /**
     * Approve the Muqquir registration.
     */
    public function approve($id)
    {
        $muqquir = MuqquirProfile::findOrFail($id);
        $muqquir->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Muqquir registration approved successfully.');
    }

    /**
     * Reject the Muqquir registration.
     */
    public function reject($id)
    {
        $muqquir = MuqquirProfile::findOrFail($id);
        $muqquir->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Muqquir registration rejected.');
    }

    /**
     * Delete the Muqquir registration.
     */
    public function destroy($id)
    {
        $muqquir = MuqquirProfile::findOrFail($id);
        
        // Delete associated videos
        foreach ($muqquir->videos as $video) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($video->video_path);
            $video->delete();
        }

        $muqquir->delete();

        return redirect()->route('admin.muqquirs.index')->with('success', 'Muqquir registration deleted.');
    }
}
