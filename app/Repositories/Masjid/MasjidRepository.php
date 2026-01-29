<?php

// app/Repositories/Masjid/MasjidRepository.php

namespace App\Repositories\Masjid;

use App\Models\Masjid;

class MasjidRepository implements MasjidRepositoryInterface
{
    public function create(array $data): Masjid
    {
        return Masjid::create($data);
    }
}
