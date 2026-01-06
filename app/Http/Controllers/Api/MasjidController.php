<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use Illuminate\Http\Request;

class MasjidController extends Controller
{
    public function show()
    {
        $majids = Masjid::select('id','name')->get();
        return response($majids);
    }
}
