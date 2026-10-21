# Backend Implementation Plan: Donor Request & Review Workflow

## 1. Executive Summary
This document provides the complete architecture and implementation specifications for the **Donor Request & Review Workflow** in the Laravel backend (`AL-Sidra`).

### Problem & Objective:
Previously, users could directly self-assign as donors via `select-place`, which bypassed Masjid committee oversight and forced users to select their own Mohalla.
This update introduces a formal **Donor Request & Verification System**:
1. **User Application**: A user applies to become a donor for a Masjid by submitting their **Father's Name** and **Unit Count** (number of family members in their household) alongside contact information. Users do NOT select a Mohalla.
2. **Mutawalli Review**: The Mutawalli / Masjid administrator receives this application as an incoming request.
3. **Mutawalli Actions**:
   - **View Details**: Full applicant profile, father's name, family unit count, phone number, and submission date.
   - **Edit Request Details**: Mutawalli can correct minor mistakes (e.g., spelling in father's name, adjustment to family units count, contact number) prior to or upon approval.
   - **Accept / Approve**: Mutawalli accepts the request and assigns the donor to their designated **Mohalla**. This promotes the user to an active donor for the Masjid.
   - **Reject**: Mutawalli can reject the request with an optional reason (e.g., outside jurisdiction, duplicate).
4. **Notifications**: The applicant is notified when their application is approved or rejected.

---

## 2. Database Schema & Migration

### Migration File: `database/migrations/2026_10_23_000001_create_donor_requests_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_requests', function (Blueprint $table) {
            $table->id();

            // Applicant User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Target Masjid (or Madarsa)
            $table->foreignId('masjid_id')->nullable()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->constrained('madarsas')->onDelete('cascade');

            // Required Community Fields
            $table->string('applicant_name');          // Pre-filled from user, editable by Mutawalli
            $table->string('father_name');             // Father's name of the applicant (Required)
            $table->unsignedInteger('unit_count')->default(1); // Number of family members / units
            $table->string('phone')->nullable();       // Contact phone number

            // Mohalla Assignment (Assigned by Mutawalli upon acceptance)
            $table->foreignId('mohalla_id')->nullable()->constrained('moallas')->nullOnDelete();
            $table->string('mohalla')->nullable();     // Cached Mohalla name

            // Workflow Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Review Details
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();             // Optional notes from user or Mutawalli
            $table->text('rejection_reason')->nullable();  // Reason if rejected

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['masjid_id', 'status']);
            $table->index(['user_id', 'masjid_id', 'status']);
            $table->index(['madarsa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_requests');
    }
};
```

---

## 3. Eloquent Model

### New Model: `app/Models/DonorRequest.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonorRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'masjid_id',
        'madarsa_id',
        'applicant_name',
        'father_name',
        'unit_count',
        'phone',
        'mohalla_id',
        'mohalla',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'unit_count'  => 'integer',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function masjid()
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class, 'madarsa_id');
    }

    public function mohallaRelation()
    {
        return $this->belongsTo(Moalla::class, 'mohalla_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForMasjid($query, $masjidId)
    {
        return $query->where('masjid_id', $masjidId);
    }
}
```

---

## 4. API Endpoints & Routes

### Route Definitions: `routes/api.php`

```php
use App\Http\Controllers\DonorRequestController;

Route::middleware('auth:sanctum')->group(function () {
    // User-side Endpoints
    Route::post('/donor-requests', [DonorRequestController::class, 'submit']);
    Route::get('/donor-requests/my-status', [DonorRequestController::class, 'myStatus']);

    // Mutawalli / Management Endpoints
    Route::prefix('masjid-management')->group(function () {
        Route::get('/donor-requests', [DonorRequestController::class, 'index']);
        Route::get('/donor-requests/{id}', [DonorRequestController::class, 'show']);
        Route::put('/donor-requests/{id}', [DonorRequestController::class, 'update']);
        Route::post('/donor-requests/{id}/approve', [DonorRequestController::class, 'approve']);
        Route::post('/donor-requests/{id}/reject', [DonorRequestController::class, 'reject']);
    });
});
```

---

## 5. Controller Implementation

### New Controller: `app/Http/Controllers/DonorRequestController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\DonorRequest;
use App\Models\Masjid;
use App\Models\Moalla;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonorRequestController extends Controller
{
    /**
     * Submit a new donor application (User-facing)
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'masjid_id'       => 'nullable|exists:masjids,id',
            'madarsa_id'      => 'nullable|exists:madarsas,id',
            'father_name'     => 'required|string|max:255',
            'unit_count'      => 'required|integer|min:1|max:50',
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

        // Check if there is already a pending request for this masjid
        $existing = DonorRequest::where('user_id', $userId)
            ->where('masjid_id', $validated['masjid_id'] ?? null)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending donor application for this Masjid. Please wait for Mutawalli review.',
                'data'    => $existing,
            ], 409);
        }

        $donorRequest = DonorRequest::create([
            'user_id'        => $userId,
            'masjid_id'      => $validated['masjid_id'] ?? null,
            'madarsa_id'     => $validated['madarsa_id'] ?? null,
            'applicant_name' => $applicantName,
            'father_name'    => trim($validated['father_name']),
            'unit_count'     => $validated['unit_count'],
            'phone'          => $phone,
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donor application submitted successfully. The Masjid Mutawalli will review your request.',
            'data'    => $donorRequest->load('user', 'masjid'),
        ], 201);
    }

    /**
     * Get user's current request status for a masjid
     */
    public function myStatus(Request $request)
    {
        $request->validate([
            'masjid_id' => 'required|exists:masjids,id',
        ]);

        $latestRequest = DonorRequest::where('user_id', $request->user()->id)
            ->where('masjid_id', $request->masjid_id)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $latestRequest,
        ]);
    }

    /**
     * Mutawalli: List all donor requests for the Masjid
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'masjid_id' => 'required|exists:masjids,id',
            'status'    => 'nullable|in:pending,approved,rejected,all',
        ]);

        $masjidId = $validated['masjid_id'];
        $status = $validated['status'] ?? 'pending';

        $query = DonorRequest::with(['user', 'mohallaRelation', 'reviewer'])
            ->where('masjid_id', $masjidId)
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20);

        // Pending counts for badges
        $pendingCount = DonorRequest::where('masjid_id', $masjidId)->where('status', 'pending')->count();

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
        $donorRequest = DonorRequest::with(['user', 'masjid', 'mohallaRelation', 'reviewer'])->findOrFail($id);

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
            'unit_count'     => 'required|integer|min:1|max:50',
            'applicant_name' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:25',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $donorRequest->update([
            'father_name'    => trim($validated['father_name']),
            'unit_count'     => $validated['unit_count'],
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
            'mohalla_id' => 'required|exists:moallas,id',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $mohalla = Moalla::findOrFail($validated['mohalla_id']);

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
                $user->update([
                    'selected_masjid_id' => $donorRequest->masjid_id,
                    'mohalla_id'         => $mohalla->id,
                    'mohalla'            => $mohalla->name,
                    'role'               => 'donor',
                ]);
            }
        });

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

        $donorRequest->update([
            'status'              => 'rejected',
            'rejection_reason'    => $validated['rejection_reason'] ?? 'Application not approved by committee',
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at'         => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donor request rejected.',
            'data'    => $donorRequest->fresh(['user', 'reviewer']),
        ]);
    }
}
```

---

## 6. Implementation Checklist for Backend Agent
1. Run migration: `php artisan make:migration create_donor_requests_table` & `php artisan migrate`.
2. Create Eloquent model `app/Models/DonorRequest.php`.
3. Create Controller `app/Http/Controllers/DonorRequestController.php`.
4. Register routes in `routes/api.php` under `auth:sanctum`.
5. Verify approval updates `users` table (`selected_masjid_id`, `mohalla_id`, `mohalla`, `role='donor'`).
