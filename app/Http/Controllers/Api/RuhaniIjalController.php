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
     * Register as an Aamil with multiple categories
     */
    public function registerAamil(Request $request): JsonResponse
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:ruhani_ijal_categories,id',
            'experience' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();

        // Check if user already has a pending or approved registration
        $existingRegistration = RuhaniIjalAamil::where('user_id', $userId)->first();

        if ($existingRegistration) {
            // Update categories and info if they want to re-apply/update? 
            // Or just block if pending/approved.
            if ($existingRegistration->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active registration. Status: ' . $existingRegistration->status,
                ], 400);
            }
            
            // If rejected, allow update
            $existingRegistration->update([
                'experience' => $request->experience,
                'description' => $request->description,
                'status' => 'pending'
            ]);
            $existingRegistration->categories()->sync($request->category_ids);
            $aamil = $existingRegistration;
        } else {
            // Create new registration
            $aamil = RuhaniIjalAamil::create([
                'user_id' => $userId,
                'experience' => $request->experience,
                'description' => $request->description,
                'status' => 'pending'
            ]);
            $aamil->categories()->attach($request->category_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Aamil registration submitted successfully',
            'data' => $aamil->load('categories')
        ]);
    }
}
