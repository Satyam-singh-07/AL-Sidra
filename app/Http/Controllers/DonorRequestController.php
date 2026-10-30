<?php

namespace App\Http\Controllers;

use App\Models\DonorRequest;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\Role;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonorRequestController extends Controller
{
    protected FirebaseNotificationService $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Submit a new donor application (User-facing)
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'masjid_id'       => 'nullable|exists:masjids,id',
            'madarsa_id'      => 'nullable|exists:madarsas,id',
            'father_name'     => 'required|string|max:255',
            'unit_count'      => 'nullable|integer|min:0|max:50000',
            'family_count'    => 'nullable|integer|min:0|max:50000',
            'nikkah_count'    => 'nullable|integer|min:0|max:50000',
            'land_count'      => 'nullable|numeric|min:0|max:1000000',
            'applicant_name'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:25',
            'notes'           => 'nullable|string|max:1000',
        ]);

        if (empty($validated['masjid_id']) && empty($validated['madarsa_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either masjid_id or madarsa_id must be provided.',
            ], 422);
        }

        $userId = $request->user()->id;
        $applicantName = $validated['applicant_name'] ?? $request->user()->name;
        $phone = $validated['phone'] ?? $request->user()->phone;

        $familyCount = (int)($validated['family_count'] ?? $validated['unit_count'] ?? 0);
        $nikkahCount = (int)($validated['nikkah_count'] ?? 0);
        $landCount = (float)($validated['land_count'] ?? 0);
        $unitCount = $familyCount;

        // Check if there is already a pending request for this masjid/madarsa
        $existingQuery = DonorRequest::where('user_id', $userId)
            ->where('status', 'pending');

        if (!empty($validated['masjid_id'])) {
            $existingQuery->where('masjid_id', $validated['masjid_id']);
        } elseif (!empty($validated['madarsa_id'])) {
            $existingQuery->where('madarsa_id', $validated['madarsa_id']);
        }

        $existing = $existingQuery->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending donor application for this place. Please wait for Mutawalli review.',
                'data'    => $existing,
            ], 409);
        }

        $donorRequest = DonorRequest::create([
            'user_id'        => $userId,
            'masjid_id'      => $validated['masjid_id'] ?? null,
            'madarsa_id'     => $validated['madarsa_id'] ?? null,
            'applicant_name' => $applicantName,
            'father_name'    => trim($validated['father_name']),
            'unit_count'     => $unitCount,
            'family_count'   => $familyCount,
            'nikkah_count'   => $nikkahCount,
            'land_count'     => $landCount,
            'phone'          => $phone,
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donor application submitted successfully. The Masjid Mutawalli will review your request.',
            'data'    => $donorRequest->load(['user', 'masjid', 'madarsa']),
        ], 201);
    }

    /**
     * Get user's current request status for a masjid or madarsa
     */
    public function myStatus(Request $request)
    {
        $request->validate([
            'masjid_id'  => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
        ]);

        $masjidId = $request->input('masjid_id');
        $madarsaId = $request->input('madarsa_id');

        if (!$masjidId && !$madarsaId) {
            return response()->json([
                'success' => false,
                'message' => 'Either masjid_id or madarsa_id is required.',
            ], 422);
        }

        $query = DonorRequest::with(['masjid', 'madarsa', 'mohallaRelation'])
            ->where('user_id', $request->user()->id);

        if ($masjidId) {
            $query->where('masjid_id', $masjidId);
        } else {
            $query->where('madarsa_id', $madarsaId);
        }

        $latestRequest = $query->latest()->first();

        return response()->json([
            'success' => true,
            'data'    => $latestRequest,
        ]);
    }

    /**
     * Mutawalli: List all donor requests for the Masjid/Madarsa
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'masjid_id'  => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'status'     => 'nullable|in:pending,approved,rejected,all',
        ]);

        $masjidId = $validated['masjid_id'] ?? $request->input('place_id');
        $madarsaId = $validated['madarsa_id'] ?? null;
        $status = $validated['status'] ?? 'pending';

        if (!$masjidId && !$madarsaId) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either masjid_id or madarsa_id.',
            ], 422);
        }

        $query = DonorRequest::with(['user', 'mohallaRelation', 'reviewer'])->latest();

        if ($masjidId) {
            $query->where('masjid_id', $masjidId);
            $pendingCount = DonorRequest::where('masjid_id', $masjidId)->where('status', 'pending')->count();
        } else {
            $query->where('madarsa_id', $madarsaId);
            $pendingCount = DonorRequest::where('madarsa_id', $madarsaId)->where('status', 'pending')->count();
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20);

        return response()->json([
            'success'       => true,
            'pending_count' => $pendingCount,
            'data'          => $requests->items(),
            'pagination'    => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    /**
     * Mutawalli: Get single request details
     */
    public function show($id)
    {
        $donorRequest = DonorRequest::with(['user', 'masjid', 'madarsa', 'mohallaRelation', 'reviewer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $donorRequest,
        ]);
    }

    /**
     * Mutawalli: Edit / correct donor request details (father's name, unit count, etc.)
     */
    public function update(Request $request, $id)
    {
        $donorRequest = DonorRequest::findOrFail($id);

        $validated = $request->validate([
            'father_name'    => 'required|string|max:255',
            'unit_count'     => 'nullable|integer|min:0|max:50000',
            'family_count'   => 'nullable|integer|min:0|max:50000',
            'nikkah_count'   => 'nullable|integer|min:0|max:50000',
            'land_count'     => 'nullable|numeric|min:0|max:1000000',
            'applicant_name' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:25',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $familyCount = isset($validated['family_count'])
            ? (int)$validated['family_count']
            : (isset($validated['unit_count']) ? (int)$validated['unit_count'] : ($donorRequest->family_count ?? $donorRequest->unit_count ?? 0));
        $nikkahCount = isset($validated['nikkah_count'])
            ? (int)$validated['nikkah_count']
            : ($donorRequest->nikkah_count ?? 0);
        $landCount = isset($validated['land_count'])
            ? (float)$validated['land_count']
            : ($donorRequest->land_count ?? 0);
        $unitCount = $familyCount;

        $donorRequest->update([
            'father_name'    => trim($validated['father_name']),
            'unit_count'     => $unitCount,
            'family_count'   => $familyCount,
            'nikkah_count'   => $nikkahCount,
            'land_count'     => $landCount,
            'applicant_name' => $validated['applicant_name'] ?? $donorRequest->applicant_name,
            'phone'          => $validated['phone'] ?? $donorRequest->phone,
            'notes'          => $validated['notes'] ?? $donorRequest->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donor request details updated successfully.',
            'data'    => $donorRequest->fresh(['user', 'mohallaRelation']),
        ]);
    }

    /**
     * Mutawalli: Accept and approve donor request
     */
    public function approve(Request $request, $id)
    {
        $donorRequest = DonorRequest::findOrFail($id);

        $validated = $request->validate([
            'mohalla_id' => 'required|exists:mohallas,id',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $mohalla = Mohalla::findOrFail($validated['mohalla_id']);

        DB::transaction(function () use ($donorRequest, $mohalla, $validated, $request) {
            // 1. Update Donor Request status
            $donorRequest->update([
                'status'              => 'approved',
                'mohalla_id'          => $mohalla->id,
                'mohalla'             => $mohalla->name,
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at'         => now(),
                'notes'               => $validated['notes'] ?? $donorRequest->notes,
            ]);

            // 2. Link User to Masjid as Approved Donor with assigned Mohalla
            $user = User::find($donorRequest->user_id);
            if ($user) {
                $userUpdates = [
                    'mohalla_id' => $mohalla->id,
                    'mohalla'    => $mohalla->name,
                    'role'       => 'donor',
                ];

                if ($donorRequest->father_name) {
                    $userUpdates['father_name'] = $donorRequest->father_name;
                }
                $userUpdates['unit_count'] = $donorRequest->family_count ?? $donorRequest->unit_count ?? 0;
                $userUpdates['family_count'] = $donorRequest->family_count ?? $donorRequest->unit_count ?? 0;
                $userUpdates['nikkah_count'] = $donorRequest->nikkah_count ?? 0;
                $userUpdates['land_count'] = $donorRequest->land_count ?? 0;
                if ($donorRequest->notes) {
                    $userUpdates['notes'] = $donorRequest->notes;
                }

                if ($donorRequest->masjid_id) {
                    $userUpdates['selected_masjid_id'] = $donorRequest->masjid_id;
                }
                if ($donorRequest->madarsa_id) {
                    $userUpdates['selected_madarsa_id'] = $donorRequest->madarsa_id;
                }

                $user->update($userUpdates);

                try {
                    $donorRole = Role::firstOrCreate(['slug' => 'donor'], ['name' => 'Donor']);
                    if (!$user->roles()->where('roles.id', $donorRole->id)->exists()) {
                        $user->roles()->attach($donorRole->id);
                    }
                } catch (\Throwable $e) {
                    // Ignore pivot errors if table not seeded
                }
            }
        });

        // 3. Dispatch FCM Push Notification to applicant
        try {
            $user = User::find($donorRequest->user_id);
            if ($user) {
                $placeName = '';
                if ($donorRequest->masjid_id) {
                    $masjid = Masjid::find($donorRequest->masjid_id);
                    $placeName = $masjid ? $masjid->name : 'Masjid';
                } elseif ($donorRequest->madarsa_id) {
                    $madarsa = Madarsa::find($donorRequest->madarsa_id);
                    $placeName = $madarsa ? $madarsa->name : 'Madarsa';
                }

                $title = "Donor Application Approved";
                $body = "Alhamdulillah! Your donor application for {$placeName} has been approved. You are assigned to {$mohalla->name}.";
                $fcmData = [
                    'type'       => 'donor_request_approved',
                    'request_id' => (string)$donorRequest->id,
                    'masjid_id'  => (string)($donorRequest->masjid_id ?? ''),
                    'madarsa_id' => (string)($donorRequest->madarsa_id ?? ''),
                    'mohalla_id' => (string)$mohalla->id,
                    'mohalla'    => (string)$mohalla->name,
                ];

                $this->firebase->sendToUser($user, $title, $body, $fcmData);
            }
        } catch (\Throwable $fcmError) {
            Log::error("FCM Error on donor request approval: " . $fcmError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Donor request approved successfully. User has been added to ' . $mohalla->name . '.',
            'data'    => $donorRequest->fresh(['user', 'mohallaRelation', 'reviewer']),
        ]);
    }

    /**
     * Mutawalli: Reject donor request
     */
    public function reject(Request $request, $id)
    {
        $donorRequest = DonorRequest::findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reason = $validated['rejection_reason'] ?? 'Application not approved by committee';

        $donorRequest->update([
            'status'              => 'rejected',
            'rejection_reason'    => $reason,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at'         => now(),
        ]);

        // Dispatch FCM Push Notification to applicant
        try {
            $user = User::find($donorRequest->user_id);
            if ($user) {
                $placeName = '';
                if ($donorRequest->masjid_id) {
                    $masjid = Masjid::find($donorRequest->masjid_id);
                    $placeName = $masjid ? $masjid->name : 'Masjid';
                } elseif ($donorRequest->madarsa_id) {
                    $madarsa = Madarsa::find($donorRequest->madarsa_id);
                    $placeName = $madarsa ? $madarsa->name : 'Madarsa';
                }

                $title = "Donor Application Update";
                $body = "Your donor application for {$placeName} was not approved. Reason: {$reason}";
                $fcmData = [
                    'type'       => 'donor_request_rejected',
                    'request_id' => (string)$donorRequest->id,
                    'masjid_id'  => (string)($donorRequest->masjid_id ?? ''),
                    'madarsa_id' => (string)($donorRequest->madarsa_id ?? ''),
                    'reason'     => (string)$reason,
                ];

                $this->firebase->sendToUser($user, $title, $body, $fcmData);
            }
        } catch (\Throwable $fcmError) {
            Log::error("FCM Error on donor request rejection: " . $fcmError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Donor request rejected.',
            'data'    => $donorRequest->fresh(['user', 'reviewer']),
        ]);
    }
}
