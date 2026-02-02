<?php

namespace App\Repositories;

use App\Models\RestaurantImage;

class RestaurantImageRepository
{
    public function create(array $data)
    {
        return RestaurantImage::create($data);
    }
}
