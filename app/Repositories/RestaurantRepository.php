<?php

namespace App\Repositories;

use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class RestaurantRepository
{
    public function create(array $data)
    {
        return Restaurant::create($data);
    }

    public function listWithDistance(
        float $userLat,
        float $userLng,
        ?string $search,
        int $perPage = 10
    ) {
        $query = Restaurant::select('restaurants.*') // IMPORTANT
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
        ", [$userLat, $userLng, $userLat])

            ->where('restaurants.status', 'approved')
            ->whereNotNull('restaurants.latitude')
            ->whereNotNull('restaurants.longitude')

            // ✅ Load images properly (no limit here)
            ->with(['images' => function ($q) {
                $q->select('id', 'restaurant_id', 'image')
                    ->orderBy('id'); // ensure first image is predictable
            }]);

        // ✅ Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('restaurants.name', 'LIKE', "%{$search}%")
                    ->orWhere('restaurants.address', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Paginate
        $restaurants = $query
            ->orderBy('distance')
            ->paginate($perPage);

        // ✅ Transform collection properly
        $restaurants->getCollection()->transform(function ($restaurant) {

            $firstImage = $restaurant->images->first();

            return [
                'id'          => $restaurant->id,
                'name'        => $restaurant->name,
                'address'     => $restaurant->address,
                'image_url'   => $firstImage ? $firstImage->image_url : null,
                'distance'    => round($restaurant->distance, 2),
            ];
        });

        return $restaurants;
    }

    public function findApprovedWithDetails(int $id)
    {
        return Restaurant::query()
            ->where('id', $id)
            ->where('status', 'approved')
            ->with([
                'images:id,restaurant_id,image'
            ])
            ->first();
    }
}
