<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Services\RestaurantService;

class RestaurantController extends Controller
{
    public function store(
        StoreRestaurantRequest $request,
        RestaurantService $service
    ) {
        $restaurant = $service->create($request->validated(), $request);

        return response()->json([
            'message' => 'Restaurant submitted for approval',
            'data'    => $restaurant,
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->latitude || !$user->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'User location not set',
            ], 422);
        }

        $lat = $user->latitude;
        $lng = $user->longitude;

        $search  = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = Restaurant::select('restaurants.*')
            ->selectRaw("
            (
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(restaurants.latitude)) *
                    cos(radians(restaurants.longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(restaurants.latitude))
                )
            ) AS distance
        ", [$lat, $lng, $lat])

            ->where('restaurants.status', 'active')

            ->with(['firstImage:id,restaurant_id,image']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('restaurants.name', 'LIKE', "%{$search}%")
                    ->orWhere('restaurants.address', 'LIKE', "%{$search}%");
            });
        }

        $restaurants = $query
            ->orderBy('distance')
            ->paginate($perPage);

        $restaurants->getCollection()->transform(function ($restaurant) {

            return [
                'id'       => $restaurant->id,
                'name'     => $restaurant->name,
                'address'  => $restaurant->address,
                'image_url' => optional($restaurant->firstImage)->image_url,
                'distance' => round($restaurant->distance, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $restaurants->items(),
            'meta'    => [
                'current_page' => $restaurants->currentPage(),
                'last_page'    => $restaurants->lastPage(),
                'per_page'     => $restaurants->perPage(),
                'total'        => $restaurants->total(),
            ]
        ]);
    }

    public function restaurantDetails(
        int $id,
        RestaurantService $service
    ) {
        return response()->json([
            'data' => $service->getRestaurantDetails($id)
        ]);
    }
}
