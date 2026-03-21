<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Get masjid job categories
     */
    public function getMasjidCategories()
    {
        $categories = JobCategory::where('type', 'masjid')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Masjid job categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Get madarsa job categories
     */
    public function getMadarsaCategories()
    {
        $categories = JobCategory::where('type', 'madarsa')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Madarsa job categories retrieved successfully',
            'data' => $categories
        ]);
    }
}
