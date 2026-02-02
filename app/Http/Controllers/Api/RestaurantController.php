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
        return response()->json(
            $service->listForUser(
                auth()->user(),
                $request->get('search'),
                (int) $request->get('per_page', 10)
            )
        );
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
