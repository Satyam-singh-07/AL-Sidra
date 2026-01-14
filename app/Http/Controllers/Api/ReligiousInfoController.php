<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReligionInfo;
use Illuminate\Http\Request;

use function Laravel\Prompts\select;

class ReligiousInfoController extends Controller
{
    public function index()
    {
        $religiousInfo = ReligionInfo::where('status','active')
            ->select( 'id', 'title', 'content','category', 'image' )
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $religiousInfo
        ]);
    }
}
