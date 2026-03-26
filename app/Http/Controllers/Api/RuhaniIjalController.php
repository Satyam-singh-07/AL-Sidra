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
     * Get specific category details
     */
    public function getCategoryDetails(int $id): JsonResponse
    {
        $category = RuhaniIjalCategory::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Category details fetched successfully',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'content' => $category->description,
            ]
        ]);
    }


    /**
     * Get all approved Aamils with pagination
     * Optional filter by category_id
     */
    public function getApprovedAamils(Request $request): JsonResponse
    {
        $query = RuhaniIjalAamil::with(['user:id,name,phone,email,address', 'categories:id,name'])
            ->where('status', 'approved');

        // Optional filter by category (supports single ID, array, or comma-separated string)
        if ($request->filled('category_id')) {
            $categoryIds = is_array($request->category_id) 
                ? $request->category_id 
                : explode(',', $request->category_id);

            $query->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('ruhani_ijal_categories.id', $categoryIds);
            });
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $aamils = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Approved Aamils fetched successfully',
            'data' => $aamils->items(),
            'pagination' => [
                'total' => $aamils->total(),
                'per_page' => $aamils->perPage(),
                'current_page' => $aamils->currentPage(),
                'last_page' => $aamils->lastPage(),
                'from' => $aamils->firstItem(),
                'to' => $aamils->lastItem(),
            ]
        ]);
    }

    /**
     * Get a single approved Aamil with all linked details.
     */
    public function showAamilDetails(int $id): JsonResponse
    {
        $aamil = RuhaniIjalAamil::with([
            'categories:id,name,description',
            'user:id,name,phone,email,address,latitude,longitude',
            'user.memberProfile.category:id,name,description,status',
            'user.memberProfile.kyc',
        ])
            ->where('status', 'approved')
            ->findOrFail($id);

        // Transform categories to use 'content' instead of 'description'
        $aamil->categories->transform(function($category) {
            $category->content = $category->description;
            unset($category->description);
            return $category;
        });

        $memberProfile = optional($aamil->user)->memberProfile;
        $place = null;

        if ($memberProfile && $memberProfile->place_type && $memberProfile->place_id) {
            $placeRelation = $memberProfile->place_type === 'masjid' ? 'masjid' : 'madarsa';

            $memberProfile->load([
                'place' => function ($query) use ($placeRelation) {
                    $query->with([
                        'community',
                        'members' => function ($memberQuery) {
                            $memberQuery->select('users.id', 'users.name', 'users.phone', 'users.email', 'users.address')
                                ->with([
                                    'memberProfile.category:id,name,description,status',
                                    'memberProfile.kyc',
                                ]);
                        },
                    ]);
                },
            ]);

            $place = $memberProfile->place;

            if ($place) {
                $place->setAttribute('place_type', $placeRelation);
            }
        }

        $aamil->setRelation('place', $place);

        return response()->json([
            'success' => true,
            'message' => 'Aamil details fetched successfully',
            'data' => $aamil,
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
            if ($existingRegistration->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active registration. Status: ' . $existingRegistration->status,
                ], 400);
            }
            
            $existingRegistration->update([
                'experience' => $request->experience,
                'description' => $request->description,
                'status' => 'pending'
            ]);
            $existingRegistration->categories()->sync($request->category_ids);
            $aamil = $existingRegistration;
        } else {
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
