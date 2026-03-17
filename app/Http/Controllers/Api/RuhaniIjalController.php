<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RuhaniIjalCategory;
use App\Models\RuhaniIjalAamil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    /**
     * Register as an Aamil
     */
    public function registerAamil(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:ruhani_ijal_categories,id',
            'experience' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Take user_id from auth as requested
        $userId = auth()->id();

        // Check if already registered for this category
        $exists = RuhaniIjalAamil::where('user_id', $userId)
            ->where('ruhani_ijal_category_id', $request->category_id)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already registered for this category. Status: ' . $exists->status,
            ], 400);
        }

        $aamil = RuhaniIjalAamil::create([
            'user_id' => $userId,
            'ruhani_ijal_category_id' => $request->category_id,
            'experience' => $request->experience,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aamil registration submitted successfully',
            'data' => $aamil
        ]);
    }
}
