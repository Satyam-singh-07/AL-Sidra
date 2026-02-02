<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $restaurants = Restaurant::with(['firstImage'])
            ->latest()
            ->paginate(10);

        return view('admin.restaurants', compact('restaurants'));
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['images', 'user']);

        return view('admin.restaurants-show', compact('restaurant'));
    }

    public function approve(Restaurant $restaurant)
    {
        $restaurant->update([
            'status' => 'approved',
            'admin_remark' => null,
        ]);

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Restaurant approved successfully');
    }

    public function reject(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'admin_remark' => 'nullable|string|max:500',
        ]);

        $restaurant->update([
            'status' => 'rejected',
            'admin_remark' => $request->admin_remark,
        ]);

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Restaurant rejected');
    }
}
