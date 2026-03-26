<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Services\RestaurantService;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Http\Requests\StoreRestaurantRequest;

class RestaurantController extends Controller
{
    public function __construct(
        protected RestaurantService $restaurantService
    ) {}

    public function index(Request $request)
    {
        $restaurants = Restaurant::with(['firstImage'])
            ->latest()
            ->paginate(10);

        return view('admin.restaurants', compact('restaurants'));
    }

    public function create()
    {
        return view('admin.restaurants-create');
    }

    public function store(StoreRestaurantRequest $request)
    {
        $this->restaurantService->create(
            $request->validated(),
            $request
        );

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Restaurant created successfully');
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['images', 'user']);

        return view('admin.restaurants-show', compact('restaurant'));
    }

    public function edit(Restaurant $restaurant)
    {
        $restaurant->load(['images']);

        return view('admin.restaurants-edit', compact('restaurant'));
    }

    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant)
    {
        $this->restaurantService->update(
            $restaurant,
            $request->validated(),
            $request
        );

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Restaurant updated successfully');
    }

    public function destroy(Restaurant $restaurant)
    {
        $this->restaurantService->delete($restaurant);

        return redirect()
            ->route('restaurants.index')
            ->with('success', 'Restaurant deleted successfully');
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

    public function cycleStatus(Restaurant $restaurant)
    {
        $nextStatus = match ($restaurant->status) {
            'approved' => 'pending',
            'pending'  => 'rejected',
            'rejected' => 'approved',
            default    => 'approved',
        };

        $restaurant->update(['status' => $nextStatus]);

        return response()->json([
            'status' => $nextStatus,
        ]);
    }

    public function deleteImage(int $imageId)
    {
        $this->restaurantService->deleteImage($imageId);

        return back()->with('success', 'Image deleted successfully');
    }

    public function deleteVideo(Restaurant $restaurant)
    {
        $this->restaurantService->deleteVideo($restaurant);

        return back()->with('success', 'Video deleted successfully');
    }
}
