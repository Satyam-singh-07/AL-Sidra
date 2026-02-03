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

        $query = Madarsa::selectRaw("
            madarsas.id,
            madarsas.name,
            madarsas.address,
            madarsas.gender,
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
            ->where('madarsas.status', 'active');

        if ($request->filled('search')) {
            $query->where('madarsas.name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('gender')) {
            $query->where('madarsas.gender', $request->gender);
        }

        $query->with(['images' => function ($q) {
            $q->select('id', 'madarsa_id', 'image_path')
                ->orderBy('id')
                ->limit(1);
        }]);

        $query->orderBy('distance');

        $madarsas = $query->paginate(10);

        $madarsas->getCollection()->transform(function ($madarsa) {
            return [
                'id'       => $madarsa->id,
                'name'     => $madarsa->name,
                'address'  => $madarsa->address,
                'gender'   => $madarsa->gender,
                'image'    => $madarsa->images->first()?->image_url,
                'distance' => round($madarsa->distance, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $madarsas,
        ]);
    }

    public function showMadarsaDetails($id)
    {
        $madarsa = Madarsa::with('images')->find($id);

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
                'passbook' => $madarsa->passbook_url ?? null,
                'video' => $madarsa->video_url ?? null,
                'images' => $madarsa->images->map(fn($img) => $img->image_url),
            ],
        ]);
    }
}
