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

    public function index(RestaurantService $service)
    {
        return response()->json([
            'data' => $service->listForUser(auth()->user())
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
