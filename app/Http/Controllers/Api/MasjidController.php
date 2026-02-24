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
        $majids = Masjid::select('id', 'name')->get();
        return response($majids);
    }

    public function listMasjids(Request $request)
    {
        $user = $request->user();

        // ✅ User must have location
        if (!$user->latitude || !$user->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'User location not set',
            ], 422);
        }

        $lat = $user->latitude;
        $lng = $user->longitude;

        // Optional search
        $search = $request->query('search');

        // Pagination
        $perPage = $request->query('per_page', 10);

        // ✅ Main Query (Fixed)
        $query = Masjid::select('masjids.*') // VERY IMPORTANT
            ->selectRaw("
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

            ->where('masjids.status', 'active')

            // ✅ Load images properly
            ->with(['images' => function ($q) {
                $q->select('id', 'masjid_id', 'image_path')
                    ->orderBy('id');
            }]);

        // ✅ Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('masjids.name', 'LIKE', "%{$search}%")
                    ->orWhere('masjids.address', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Paginate Results
        $masjids = $query
            ->orderBy('distance')
            ->paginate($perPage);

        // ✅ Transform Response
        $masjids->getCollection()->transform(function ($masjid) {

            $firstImage = $masjid->images->first();

            return [
                'id'       => $masjid->id,
                'name'     => $masjid->name,
                'address'  => $masjid->address,

                'image_url'    => $firstImage ? $firstImage->image_url : null,

                'distance' => round($masjid->distance, 2),
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
