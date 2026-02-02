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
        return Restaurant::query()
            ->select([
                'restaurants.id',
                'restaurants.name',
                'restaurants.address',
                DB::raw('
                ( 6371 * acos(
                    cos(radians(' . $userLat . ')) *
                    cos(radians(restaurants.latitude)) *
                    cos(radians(restaurants.longitude) - radians(' . $userLng . ')) +
                    sin(radians(' . $userLat . ')) *
                    sin(radians(restaurants.latitude))
                ))
                AS distance
            ')
            ])
            ->where('restaurants.status', 'approved')
            ->whereNotNull('restaurants.latitude')
            ->whereNotNull('restaurants.longitude')

            // 🔍 SEARCH FILTER
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('restaurants.name', 'LIKE', "%{$search}%")
                        ->orWhere('restaurants.address', 'LIKE', "%{$search}%");
                });
            })

            ->with([
                'images' => function ($q) {
                    $q->select('id', 'restaurant_id', 'image')->limit(1);
                }
            ])
            ->orderBy('distance')
            ->paginate($perPage)
            ->through(function ($restaurant) {
                $image = $restaurant->images->first();

                return [
                    'id'         => $restaurant->id,
                    'name'       => $restaurant->name,
                    'address'    => $restaurant->address,
                    'image'      => $image?->image,
                    'image_url'  => $image?->image_url,
                    'distance'   => round($restaurant->distance, 2),
                ];
            });
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
