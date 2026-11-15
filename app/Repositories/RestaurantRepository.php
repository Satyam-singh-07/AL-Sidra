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
        ?float $userLat,
        ?float $userLng,
        ?string $search,
        int $perPage = 10
    ) {
        $hasLocation = !empty($userLat) && !empty($userLng);

        $query = Restaurant::select('restaurants.*')
            ->where('restaurants.status', 'approved')
            // ✅ Load images properly (no limit here)
            ->with(['images' => function ($q) {
                $q->select('id', 'restaurant_id', 'image')
                    ->orderBy('id'); // ensure first image is predictable
            }]);

        if ($hasLocation) {
            $query->selectRaw("
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
            ->whereNotNull('restaurants.latitude')
            ->whereNotNull('restaurants.longitude')
            ->orderBy('distance');
        } else {
            $query->selectRaw("NULL AS distance")
                ->orderBy('restaurants.id', 'desc');
        }

        // ✅ Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('restaurants.name', 'LIKE', "%{$search}%")
                    ->orWhere('restaurants.address', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Paginate
        $restaurants = $query->paginate($perPage);

        // ✅ Transform collection properly
        $restaurants->getCollection()->transform(function ($restaurant) {
            $firstImage = $restaurant->images->first();

            return [
                'id'          => $restaurant->id,
                'name'        => $restaurant->name,
                'address'     => $restaurant->address,
                'image_url'   => $firstImage ? $firstImage->image_url : null,
                'distance'    => $restaurant->distance !== null ? round((float) $restaurant->distance, 2) : 0.0,
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
