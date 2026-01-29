<?php

// app/Repositories/Masjid/MasjidRepositoryInterface.php

namespace App\Repositories\Masjid;

use App\Models\Masjid;

interface MasjidRepositoryInterface
{
    public function create(array $data): Masjid;
}
