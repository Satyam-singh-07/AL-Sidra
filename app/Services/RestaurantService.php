<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Repositories\RestaurantRepository;
use App\Repositories\RestaurantImageRepository;

class RestaurantService
{
    public function __construct(
        protected RestaurantRepository $restaurantRepo,
        protected RestaurantImageRepository $imageRepo
    ) {}

    public function create(array $data, Request $request)
    {
        return DB::transaction(function () use ($data, $request) {

            // Upload menu image
            if ($request->hasFile('menu_image')) {
                $data['menu_image'] = $request->file('menu_image')
                    ->store('restaurants/menu', 'public');
            }

            // Upload video
            if ($request->hasFile('video')) {
                $data['video'] = $request->file('video')
                    ->store('restaurants/videos', 'public');
            }

            $data['user_id'] = auth()->id();
            $data['status']  = 'pending';

            $restaurant = $this->restaurantRepo->create($data);

            // Upload multiple images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('restaurants/images', 'public');

                    $this->imageRepo->create([
                        'restaurant_id' => $restaurant->id,
                        'image'         => $path,
                    ]);
                }
            }

            return $restaurant->load('images');
        });
    }

    public function listForUser(User $user, ?string $search, int $perPage = 10)
    {
        if (!$user->latitude || !$user->longitude) {
            throw new \Exception('User location not available');
        }

        return $this->restaurantRepo->listWithDistance(
            $user->latitude,
            $user->longitude,
            $search,
            $perPage
        );
    }

    public function getRestaurantDetails(int $id)
    {
        $restaurant = $this->restaurantRepo->findApprovedWithDetails($id);

        if (!$restaurant) {
            abort(404, 'Restaurant not found');
        }

        return $restaurant;
    }
}
