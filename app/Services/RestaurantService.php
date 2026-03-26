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

    public function update(Restaurant $restaurant, array $data, Request $request): void
    {
        DB::transaction(function () use ($restaurant, $data, $request) {

            $updateData = [
                'name'           => $data['name'],
                'description'    => $data['description'] ?? null,
                'address'        => $data['address'],
                'contact_number' => $data['contact_number'],
                'opening_time'   => $data['opening_time'],
                'closing_time'   => $data['closing_time'],
                'latitude'       => $data['latitude'] ?? $restaurant->latitude,
                'longitude'      => $data['longitude'] ?? $restaurant->longitude,
                'status'         => $data['status'] ?? $restaurant->status,
            ];

            // Upload menu image
            if ($request->hasFile('menu_image')) {
                if ($restaurant->menu_image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($restaurant->menu_image);
                }
                $updateData['menu_image'] = $request->file('menu_image')
                    ->store('restaurants/menu', 'public');
            }

            // Upload video
            if ($request->hasFile('video')) {
                if ($restaurant->video) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($restaurant->video);
                }
                $updateData['video'] = $request->file('video')
                    ->store('restaurants/videos', 'public');
            }

            $restaurant->update($updateData);

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
        });
    }

    public function delete(Restaurant $restaurant): void
    {
        DB::transaction(function () use ($restaurant) {

            foreach ($restaurant->images as $image) {
                if ($image->image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }

            if ($restaurant->menu_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($restaurant->menu_image);
            }

            if ($restaurant->video) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($restaurant->video);
            }

            $restaurant->delete();
        });
    }

    public function deleteImage(int $imageId): void
    {
        $image = \App\Models\RestaurantImage::findOrFail($imageId);
        if ($image->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image);
        }
        $image->delete();
    }

    public function deleteVideo(Restaurant $restaurant): void
    {
        if ($restaurant->video) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($restaurant->video);
            $restaurant->update(['video' => null]);
        }
    }
}
