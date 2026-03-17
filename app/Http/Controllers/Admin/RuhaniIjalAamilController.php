<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuhaniIjalAamil;
use Illuminate\Http\Request;

class RuhaniIjalAamilController extends Controller
{
    /**
     * Display a listing of Aamil registrations.
     */
    public function index()
    {
        $aamils = RuhaniIjalAamil::with(['user', 'categories'])->latest()->get();
        return view('admin.ruhani-ijal-aamils', compact('aamils'));
    }

    /**
     * Display the specified Aamil registration details.
     */
    public function show($id)
    {
        $aamil = RuhaniIjalAamil::with(['user', 'categories'])->findOrFail($id);
        return view('admin.ruhani-ijal-aamil-show', compact('aamil'));
    }

    /**
     * Approve the Aamil registration.
     */
    public function approve($id)
    {
        $aamil = RuhaniIjalAamil::findOrFail($id);
        $aamil->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Aamil registration approved successfully.');
    }

    /**
     * Reject the Aamil registration.
     */
    public function reject($id)
    {
        $aamil = RuhaniIjalAamil::findOrFail($id);
        $aamil->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Aamil registration rejected.');
    }

    /**
     * Delete the Aamil registration.
     */
    public function destroy($id)
    {
        $aamil = RuhaniIjalAamil::findOrFail($id);
        $aamil->delete();

        return redirect()->route('ruhani-ijal.index')->with('success', 'Aamil registration deleted.');
    }
}
