<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMadarsaRequest;
use App\Models\Madarsa;
use App\Services\MadarsaService;
use Illuminate\Http\Request;

class MadarsaController extends Controller
{
    public function show()
    {
        $madarsas = Madarsa::select('id', 'name')->get();
        return response($madarsas);
    }

    public function store(StoreMadarsaRequest $request, MadarsaService $service)
    {
        $data = $request->validated();

        $data['status'] = 'pending';

        $service->create(
            $data,
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Madarsa request submitted successfully.'
        ], 201);
    }

    public function listMadarsas(Request $request)
    {
        $user = $request->user();

        if (!$user->latitude || !$user->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'User location not set',
            ], 422);
        }

        $lat = $user->latitude;
        $lng = $user->longitude;
        $perPage = $request->query('per_page', 10);

        $query = Madarsa::select('madarsas.*')
            ->selectRaw("
            (
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(madarsas.latitude)) *
                    cos(radians(madarsas.longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(madarsas.latitude))
                )
            ) AS distance
        ", [$lat, $lng, $lat])

            ->where('madarsas.status', 'active')
            ->whereNotNull('madarsas.latitude')
            ->whereNotNull('madarsas.longitude')

            ->with(['firstImage:id,madarsa_id,image_path']);

        // 🔍 Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('madarsas.name', 'LIKE', "%{$search}%")
                    ->orWhere('madarsas.address', 'LIKE', "%{$search}%");
            });
        }

        // 👤 Gender filter
        if ($request->filled('gender')) {
            $query->where('madarsas.gender', $request->gender);
        }

        $madarsas = $query
            ->orderBy('distance')
            ->paginate($perPage);

        // ✅ Transform response
        $madarsas->getCollection()->transform(function ($madarsa) {
            return [
                'id'        => $madarsa->id,
                'name'      => $madarsa->name,
                'address'   => $madarsa->address,
                'gender'    => $madarsa->gender,
                'image_url' => $madarsa->firstImage?->image_url,
                'distance'  => round($madarsa->distance, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $madarsas->items(),
            'meta'    => [
                'current_page' => $madarsas->currentPage(),
                'last_page'    => $madarsas->lastPage(),
                'per_page'     => $madarsas->perPage(),
                'total'        => $madarsas->total(),
            ]
        ]);
    }

    public function showMadarsaDetails($id)
    {
        $madarsa = Madarsa::with([
            'images',
            'members.memberProfile.category'
        ])->find($id);

        if (!$madarsa) {
            return response()->json([
                'success' => false,
                'message' => 'Madarsa not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $madarsa->id,
                'name' => $madarsa->name,
                'gender' => $madarsa->gender,
                'address' => $madarsa->address,
                'latitude' => $madarsa->latitude,
                'longitude' => $madarsa->longitude,
                'registration_number' => $madarsa->registration_number,
                'registration_date' => $madarsa->registration_date,
                'passbook' => $madarsa->passbook_url,
                'video' => $madarsa->video_url,

                'images' => $madarsa->images->map(fn($img) => $img->image_url),

                // ✅ Members Connected
                'members' => $madarsa->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'phone' => $member->phone,
                        'category' => $member->memberProfile->category->name ?? null,
                        'kyc_status' => $member->memberProfile->kyc_status ?? null,
                    ];
                }),
            ],
        ]);
    }
}
