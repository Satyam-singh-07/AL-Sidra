<?php

namespace App\Repositories;

use App\Models\YateemsHelp;

class YateemsHelpRepository
{
    public function create(array $data): YateemsHelp
    {
        return YateemsHelp::create($data);
    }

    public function update(YateemsHelp $yateemsHelp, array $data): YateemsHelp
    {
        $yateemsHelp->update($data);
        return $yateemsHelp;
    }
}
