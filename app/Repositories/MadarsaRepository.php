<?php

namespace App\Repositories;

use App\Models\Madarsa;
use App\Models\MadarsaImage;

class MadarsaRepository
{
    public function create(array $data): Madarsa
    {
        unset($data['madarsa_images']); // not a DB column

        return Madarsa::create($data);
    }

    public function addImage(int $madarsaId, string $path): MadarsaImage
    {
        return MadarsaImage::create([
            'madarsa_id' => $madarsaId,
            'image_path' => $path,
        ]);
    }

    public function update(Madarsa $madarsa, array $data): Madarsa
    {
        unset($data['madarsa_images']);

        $madarsa->update($data);

        return $madarsa;
    }
}
