<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use Illuminate\Http\Request;

class MadarsaController extends Controller
{
    public function show()
    {
        $madarsas = Madarsa::select('id','name')->get();
        return response($madarsas);
    }
}
