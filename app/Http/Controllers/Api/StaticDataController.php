<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaticDataController extends Controller
{
    public function surah()
    {
        return response()->json([
            'data' => config('surahs')
        ]);
    }
   
    public function pages()
    {
        return response()->json([
            'data' => config('pages')
        ]);
    }

    public function juzs()
    {
        return response()->json([
            'data' => config('juzs')
        ]);
    }

    public function hizb()
    {
        return response()->json([
            'data' => config('hizbs')
        ]);
    }

    public function stac()
    {
        return response()->json(config('stacs'));
    }
}
