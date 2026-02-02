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

    public function listWithDistance(float $userLat, float $userLng)
    {
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
            ->with([
                'images' => function ($q) {
                    $q->select('id', 'restaurant_id', 'image')
                        ->limit(1);
                }
            ])
            ->orderBy('distance')
            ->get()
            ->map(function ($restaurant) {
                return [
                    'id'       => $restaurant->id,
                    'name'     => $restaurant->name,
                    'address'  => $restaurant->address,
                    'image'    => $restaurant->images->first()->image ?? null,
                    'image_url' => optional($restaurant->images->first())->image_url,
                    'distance' => round($restaurant->distance, 2),
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
