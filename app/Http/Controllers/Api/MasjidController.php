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

        if (!$user->latitude || !$user->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'User location not set',
            ], 422);
        }

        $lat = $user->latitude;
        $lng = $user->longitude;

        $masjids = Masjid::selectRaw("
            masjids.id,
            masjids.name,
            masjids.address,
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
            ->with(['images' => function ($q) {
                $q->select('id', 'masjid_id', 'image_path')
                    ->orderBy('id')
                    ->limit(1);
            }])
            ->orderBy('distance')
            ->get()
            ->map(function ($masjid) {
                return [
                    'id'       => $masjid->id,
                    'name'     => $masjid->name,
                    'address'  => $masjid->address,
                    'image'    => $masjid->images->first()
                        ? asset('storage/' . $masjid->images->first()->image_path)
                        : null,
                    'distance' => round($masjid->distance, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $masjids,
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
        $masjid = Masjid::with('images')->find($id);

        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $masjid,
        ]);
    }
}
