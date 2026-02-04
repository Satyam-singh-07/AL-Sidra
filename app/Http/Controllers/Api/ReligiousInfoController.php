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
            ->select( 'id', 'title' )
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $religiousInfo
        ]);
    }

    public function show($id)
    {
        $info = ReligionInfo::where('id', $id)
            ->where('status', 'active')
            ->first();

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => 'Religious information not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $info
        ]);
    }
}
