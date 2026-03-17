<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RuhaniIjalAamil;
use App\Models\RuhaniIjalCategory;
use Illuminate\Http\Request;

class RuhaniIjalController extends Controller
{
    /**
     * Display a listing of Aamil registrations on the main Ruhani Ijal page.
     */
    public function index(Request $request)
    {
        $query = RuhaniIjalAamil::with(['user', 'categories']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('ruhani_ijal_categories.id', $request->category);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $aamils = $query->latest()->get();

        // Statistics
        $stats = [
            'total' => RuhaniIjalAamil::count(),
            'pending' => RuhaniIjalAamil::where('status', 'pending')->count(),
            'approved' => RuhaniIjalAamil::where('status', 'approved')->count(),
            'rejected' => RuhaniIjalAamil::where('status', 'rejected')->count(),
        ];

        $categories = RuhaniIjalCategory::all();

        return view('admin.ruhani-ijal', compact('aamils', 'stats', 'categories'));
    }
}
