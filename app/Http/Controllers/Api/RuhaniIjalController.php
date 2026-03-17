<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RuhaniIjalCategory;
use Illuminate\Http\JsonResponse;

class RuhaniIjalController extends Controller
{
    /**
     * Get all Ruhani Ijal categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = RuhaniIjalCategory::all(['id', 'name', 'description']);

        return response()->json([
            'success' => true,
            'message' => 'Ruhani Ijal categories fetched successfully',
            'data' => $categories
        ]);
    }
}
