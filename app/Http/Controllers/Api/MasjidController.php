<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMasjidRequest;
use App\Services\MasjidService;

class MasjidController extends Controller
{
    public function show()
    {
        $majids = Masjid::select('id', 'name', 'unique_id')->get();
        return response($majids);
    }

    public function listMasjids(Request $request)
    {
        $user = $request->user();

        // Support live latitude and longitude query params, falling back to user's saved location
        $lat = $request->query('latitude') !== null ? (float) $request->query('latitude') : ($user?->latitude ? (float) $user->latitude : null);
        $lng = $request->query('longitude') !== null ? (float) $request->query('longitude') : ($user?->longitude ? (float) $user->longitude : null);
        $hasLocation = !empty($lat) && !empty($lng);

        // Optional search
        $search = $request->query('search');

        // Pagination
        $perPage = $request->query('per_page', 10);

        // ✅ Main Query
        $query = Masjid::select('masjids.*')
            ->where('masjids.status', 'active')
            // ✅ Load images properly
            ->with(['images' => function ($q) {
                $q->select('id', 'masjid_id', 'image_path')
                    ->orderBy('id');
            }]);

        if ($hasLocation) {
            $query->selectRaw("
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(masjids.latitude)) *
                        cos(radians(masjids.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(masjids.latitude))
                    )
                ) AS distance
            ", [$lat, $lng, $lat])
            ->orderBy('distance');
        } else {
            $query->selectRaw("NULL AS distance")
                ->orderBy('masjids.id', 'desc');
        }

        // ✅ Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('masjids.name', 'LIKE', "%{$search}%")
                    ->orWhere('masjids.address', 'LIKE', "%{$search}%")
                    ->orWhere('masjids.unique_id', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Paginate Results
        $masjids = $query->paginate($perPage);

        // ✅ Transform Response
        $masjids->getCollection()->transform(function ($masjid) {
            $firstImage = $masjid->images->first();

            return [
                'id'       => $masjid->id,
                'unique_id' => $masjid->unique_id,
                'name'     => $masjid->name,
                'address'  => $masjid->address,
                'image_url'    => $firstImage ? $firstImage->image_url : null,
                'distance' => $masjid->distance !== null ? round((float) $masjid->distance, 2) : 0.0,
            ];
        });

        // ✅ Final API Response
        return response()->json([
            'success' => true,
            'data'    => $masjids->items(),
            'meta'    => [
                'current_page' => $masjids->currentPage(),
                'last_page'    => $masjids->lastPage(),
                'per_page'     => $masjids->perPage(),
                'total'        => $masjids->total(),
            ]
        ]);
    }

    public function store(StoreMasjidRequest $request, MasjidService $service)
    {
        $masjid = $service->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => $masjid,
        ], 201);
    }

    public function showMasjidDetails($id)
    {
        $masjid = Masjid::with([
            'images',
            'community',
            'members.memberProfile.category'
        ])->find($id);

        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'masjid' => $masjid,
                'members' => $masjid->members->map(function ($member) {
                    return [
                        'id'       => $member->id,
                        'unique_id' => $member->unique_id,
                        'name'     => $member->name,
                        'phone'    => $member->phone,
                        'category' => $member->memberProfile->category->name ?? null,
                        'kyc_status' => $member->memberProfile->kyc_status,
                    ];
                })
            ]
        ]);
    }
}
