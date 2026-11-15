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
        $madarsas = Madarsa::select('id', 'name', 'unique_id')->get();
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

        // Support live latitude and longitude query params, falling back to user's saved location
        $lat = $request->query('latitude') !== null ? (float) $request->query('latitude') : ($user?->latitude ? (float) $user->latitude : null);
        $lng = $request->query('longitude') !== null ? (float) $request->query('longitude') : ($user?->longitude ? (float) $user->longitude : null);
        $hasLocation = !empty($lat) && !empty($lng);

        $query = Madarsa::where('madarsas.status', 'active');

        if ($hasLocation) {
            $query->selectRaw("
                madarsas.id,
                madarsas.unique_id,
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
            ->whereNotNull('madarsas.latitude')
            ->whereNotNull('madarsas.longitude')
            ->orderBy('distance');
        } else {
            $query->selectRaw("
                madarsas.id,
                madarsas.unique_id,
                madarsas.name,
                madarsas.address,
                madarsas.gender,
                NULL AS distance
            ")
            ->orderBy('madarsas.id', 'desc');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('madarsas.name', 'like', '%' . $request->search . '%')
                    ->orWhere('madarsas.unique_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('gender')) {
            $query->where('madarsas.gender', $request->gender);
        }

        // ✅ FIXED eager loading (no limit here)
        $query->with(['images' => function ($q) {
            $q->select(
                'madarsa_images.id',
                'madarsa_images.madarsa_id',
                'madarsa_images.image_path'
            )->orderBy('madarsa_images.id');
        }]);

        $madarsas = $query->paginate(10);

        $madarsas->getCollection()->transform(function ($madarsa) {
            return [
                'id'       => $madarsa->id,
                'unique_id' => $madarsa->unique_id,
                'name'     => $madarsa->name,
                'address'  => $madarsa->address,
                'gender'   => $madarsa->gender,
                'image'    => $madarsa->images->first()?->image_url,
                'distance' => $madarsa->distance !== null ? round((float) $madarsa->distance, 2) : 0.0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $madarsas,
        ]);
    }


    public function showMadarsaDetails($id)
    {
        $madarsa = Madarsa::with([
            'images',
            'members.memberProfile.category',
            'courses',
            'collectors',
            'community'
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
                'unique_id' => $madarsa->unique_id,
                'name' => $madarsa->name,
                'gender' => $madarsa->gender,
                'address' => $madarsa->address,
                'latitude' => $madarsa->latitude,
                'longitude' => $madarsa->longitude,
                'students_count' => $madarsa->students_count,
                'staff_count' => $madarsa->staff_count,
                'registration_number' => $madarsa->registration_number,
                'registration_date' => $madarsa->registration_date,
                'passbook' => $madarsa->passbook_url,
                'video' => $madarsa->video_url,

                'images' => $madarsa->images->map(fn($img) => $img->image_url),

                'community' => $madarsa->community ? [   // Added community block
                    'id' => $madarsa->community->id,
                    'name' => $madarsa->community->name,
                ] : null,

                'courses' => $madarsa->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'description' => $course->description,
                    ];
                }),

                'collectors' => $madarsa->collectors->map(function ($collector) {
                    return [
                        'id' => $collector->id,
                        'name' => $collector->name,
                        'contact' => $collector->contact,
                        'address' => $collector->address,
                    ];
                }),

                'members' => $madarsa->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'unique_id' => $member->unique_id,
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
