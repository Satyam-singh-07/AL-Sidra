<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use App\Models\MasjidCampaignCategory;
use Illuminate\Http\Request;

class MasjidCampaignCategoryController extends Controller
{
    /**
     * Get all active campaign categories for a masjid
     * Route: GET /api/masjid/{id}/campaign-categories
     */
    public function index(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        // If no categories exist yet for this masjid, initialize baseline default categories
        $count = MasjidCampaignCategory::where('masjid_id', $masjidId)->count();
        if ($count === 0) {
            $defaultCategories = [
                [
                    'masjid_id'   => $masjidId,
                    'name'        => 'Family Unit (افراد)',
                    'unit_label'  => 'Unit',
                    'description' => 'Household contributing members count',
                    'is_active'   => true,
                ],
                [
                    'masjid_id'   => $masjidId,
                    'name'        => 'Zameen Par (بیگھہ)',
                    'unit_label'  => 'Beegah',
                    'description' => 'Agricultural / Land holdings in Beegah',
                    'is_active'   => true,
                ],
                [
                    'masjid_id'   => $masjidId,
                    'name'        => 'Nikah Par (نکاح)',
                    'unit_label'  => 'Event',
                    'description' => 'Contributions per marriage ceremony in family',
                    'is_active'   => true,
                ],
                [
                    'masjid_id'   => $masjidId,
                    'name'        => 'By Choice (اختیاری)',
                    'unit_label'  => 'Voluntary',
                    'description' => 'Open voluntary contributions',
                    'is_active'   => true,
                ],
            ];

            foreach ($defaultCategories as $cat) {
                MasjidCampaignCategory::create($cat);
            }
        }

        $categories = MasjidCampaignCategory::where('masjid_id', $masjidId)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    /**
     * Create a new custom campaign category for a masjid (Mutawalli only)
     * Route: POST /api/masjid/{id}/campaign-categories
     */
    public function store(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->isAuthorizedMutawalli($user, $masjid)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only the Mutawalli can create campaign categories.',
            ], 403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'unit_label'  => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $category = MasjidCampaignCategory::create([
            'masjid_id'   => $masjidId,
            'name'        => trim($validated['name']),
            'unit_label'  => trim($validated['unit_label'] ?? 'Unit') ?: 'Unit',
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'is_active'   => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign category created successfully.',
            'data'    => $category,
        ], 201);
    }

    /**
     * Delete / deactivate a campaign category (Mutawalli only)
     * Route: DELETE /api/masjid/{masjid_id}/campaign-categories/{id}
     */
    public function destroy(Request $request, $masjidId, $id)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->isAuthorizedMutawalli($user, $masjid)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $category = MasjidCampaignCategory::where('masjid_id', $masjidId)->find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $category->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Category removed successfully.',
        ]);
    }

    protected function isAuthorizedMutawalli($user, Masjid $masjid): bool
    {
        if ($masjid->user_id === $user->id) {
            return true;
        }

        if ($user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists()) {
            return true;
        }

        if ($user->memberProfile && (
            $user->memberProfile->masjid_id == $masjid->id ||
            ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjid->id)
        )) {
            return true;
        }

        return false;
    }
}

