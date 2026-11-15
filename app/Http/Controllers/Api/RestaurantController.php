<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
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

    public function index(Request $request, RestaurantService $service)
    {
        $user = $request->user();

        // Support live latitude and longitude query params, falling back to user's saved location
        $lat = $request->query('latitude') !== null ? (float) $request->query('latitude') : ($user?->latitude ? (float) $user->latitude : null);
        $lng = $request->query('longitude') !== null ? (float) $request->query('longitude') : ($user?->longitude ? (float) $user->longitude : null);

        $restaurants = $service->listForUser(
            $user,
            $request->query('search'),
            (int) $request->query('per_page', 10),
            $lat,
            $lng
        );

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
