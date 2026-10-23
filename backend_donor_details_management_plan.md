# Backend Implementation Plan: Donor Details Management by Donor ID

## 1. Executive Summary & Objective

### Context:
When users are listed in the **Mohalla Directory > Donors** tab, they have already been approved and are active donors associated with the Masjid. 

### Current Issue:
When a Mutawalli clicks **Edit Details** on a donor card and submits modifications (such as updating their Name, Father's Name, Household Unit Count, Phone, Mohalla, or Notes), the mobile application calls:
```http
PUT https://alsidra.in/api/masjid-donors/{id}
```
where `{id}` is the **Donor ID** (User ID, e.g., `243`).

The backend currently responds with:
```json
{
  "error_code": "RESOURCE_NOT_FOUND",
  "message": "The requested resource was not found.",
  "details": {
    "reason": "The specified URL or resource does not exist."
  }
}
```
Because the route `PUT /api/masjid-donors/{id}` has not been registered in `routes/api.php`.

### Objective:
Implement the `PUT /api/masjid-donors/{id}` endpoint in Laravel to allow Mutawallis to update an active donor's details directly using their **Donor ID**.

---

## 2. API Specification

- **Endpoint**: `PUT /api/masjid-donors/{id}` (and alias `PUT /api/masjid-management/donors/{id}`)
- **Authentication**: `Bearer <token>` (`auth:sanctum`)
- **URL Parameter**:
  - `id` (integer, required): The Donor's User ID (e.g., `243`).

### Request Body:
```json
{
  "masjid_id": 1,
  "name": "Mohammed Ali",
  "father_name": "Ghulam Ali",
  "unit_count": 4,
  "phone": "+91 9876543210",
  "mohalla_id": 3,
  "mohalla": "Noorani Chawk",
  "notes": "House #42, near water tank"
}
```

### Validation Rules:
| Field | Type | Rules | Description |
|---|---|---|---|
| `masjid_id` | integer | `required\|exists:places,id` (or `exists:masjids,id`) | Masjid context |
| `name` | string | `required\|string\|max:255` | Full legal / community name of donor |
| `father_name` | string | `nullable\|string\|max:255` | Father's name |
| `unit_count` | integer | `nullable\|integer\|min:1\|max:50` | Household unit share count |
| `phone` | string | `nullable\|string\|max:25` | Contact phone number |
| `mohalla_id` | integer | `nullable\|exists:moallas,id` | ID of assigned Mohalla |
| `mohalla` | string | `nullable\|string\|max:255` | Cached name of assigned Mohalla |
| `notes` | string | `nullable\|string\|max:1000` | Optional address/street remarks |

### Response (200 OK):
```json
{
  "success": true,
  "message": "Donor details updated successfully.",
  "data": {
    "id": 243,
    "name": "Mohammed Ali",
    "father_name": "Ghulam Ali",
    "unit_count": 4,
    "phone": "+91 9876543210",
    "email": "donor@example.com",
    "mohalla_id": 3,
    "mohalla": "Noorani Chawk",
    "notes": "House #42, near water tank"
  }
}
```

---

## 3. Database Migration (If Columns Missing on `users` table)

Check if `father_name`, `unit_count`, or `notes` exist on `users`. If missing:

```bash
php artisan make:migration add_donor_fields_to_users_table --table=users
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'father_name')) {
                $table->string('father_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'unit_count')) {
                $table->unsignedInteger('unit_count')->default(1)->after('father_name');
            }
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('mohalla');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['father_name', 'unit_count', 'notes']);
        });
    }
};
```

---

## 4. Route Registration (`routes/api.php`)

Add the route inside the `auth:sanctum` group:

```php
use App\Http\Controllers\Api\MasjidSelectController;
// OR App\Http\Controllers\DonorRequestController
// OR App\Http\Controllers\Api\DonorController

Route::middleware('auth:sanctum')->group(function () {
    // Existing donor listing:
    // Route::get('masjid-donors', [MasjidSelectController::class, 'getDonors']);
    
    // NEW: Update active donor details by Donor ID
    Route::put('/masjid-donors/{id}', [MasjidSelectController::class, 'updateDonor']);
    Route::put('/masjid-management/donors/{id}', [MasjidSelectController::class, 'updateDonor']);
});
```

---

## 5. Controller Implementation

Add `updateDonor(Request $request, $id)` in `app/Http/Controllers/Api/MasjidSelectController.php` (or your preferred donor controller):

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonorRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasjidSelectController extends Controller
{
    /**
     * Update an active donor's details using their Donor ID (User ID).
     */
    public function updateDonor(Request $request, $id)
    {
        $validated = $request->validate([
            'masjid_id'   => 'required|integer',
            'name'        => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'unit_count'  => 'nullable|integer|min:1|max:50',
            'phone'       => 'nullable|string|max:25',
            'mohalla_id'  => 'nullable|integer',
            'mohalla'     => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $masjidId = $validated['masjid_id'];
        $user = User::findOrFail($id);

        // Resolve Mohalla Name if mohalla_id is provided
        $mohallaName = $validated['mohalla'] ?? null;
        if (!empty($validated['mohalla_id'])) {
            $mohallaRecord = DB::table('moallas')->where('id', $validated['mohalla_id'])->first();
            if ($mohallaRecord) {
                $mohallaName = $mohallaRecord->name;
            }
        }

        DB::transaction(function () use ($user, $validated, $masjidId, $mohallaName) {
            // 1. Update the User record
            $user->name = trim($validated['name']);
            if (isset($validated['phone'])) {
                $user->phone = trim($validated['phone']);
            }
            if ($mohallaName !== null) {
                $user->mohalla = $mohallaName;
            }
            if (!empty($validated['mohalla_id'])) {
                $user->mohalla_id = $validated['mohalla_id'];
            }
            if (isset($validated['father_name'])) {
                $user->father_name = trim($validated['father_name']);
            }
            if (isset($validated['unit_count'])) {
                $user->unit_count = (int)$validated['unit_count'];
            }
            if (isset($validated['notes'])) {
                $user->notes = trim($validated['notes']);
            }
            $user->save();

            // 2. Update Pivot Table if places_users / place_user exists
            if (DB::getSchemaBuilder()->hasTable('places_users')) {
                DB::table('places_users')
                    ->where('user_id', $user->id)
                    ->where('place_id', $masjidId)
                    ->update(array_filter([
                        'mohalla'    => $mohallaName,
                        'mohalla_id' => $validated['mohalla_id'] ?? null,
                    ]));
            } elseif (DB::getSchemaBuilder()->hasTable('place_user')) {
                DB::table('place_user')
                    ->where('user_id', $user->id)
                    ->where('place_id', $masjidId)
                    ->update(array_filter([
                        'mohalla'    => $mohallaName,
                        'mohalla_id' => $validated['mohalla_id'] ?? null,
                    ]));
            }

            // 3. Keep associated DonorRequest records in sync if table exists
            if (DB::getSchemaBuilder()->hasTable('donor_requests')) {
                DonorRequest::where('user_id', $user->id)
                    ->where('masjid_id', $masjidId)
                    ->update(array_filter([
                        'applicant_name' => $user->name,
                        'father_name'    => $validated['father_name'] ?? null,
                        'unit_count'     => $validated['unit_count'] ?? null,
                        'phone'          => $validated['phone'] ?? null,
                        'mohalla'        => $mohallaName,
                        'mohalla_id'     => $validated['mohalla_id'] ?? null,
                        'notes'          => $validated['notes'] ?? null,
                    ]));
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Donor details updated successfully.',
            'data'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'father_name' => $user->father_name ?? $validated['father_name'] ?? null,
                'unit_count'  => $user->unit_count ?? $validated['unit_count'] ?? 1,
                'phone'       => $user->phone,
                'email'       => $user->email,
                'mohalla'     => $user->mohalla ?? $mohallaName,
                'mohalla_id'  => $user->mohalla_id ?? $validated['mohalla_id'] ?? null,
                'notes'       => $user->notes ?? $validated['notes'] ?? null,
            ],
        ], 200);
    }
}
```

---

## 6. Verification Steps

1. Send `PUT` request with donor ID:
   ```bash
   curl -X PUT https://alsidra.in/api/masjid-donors/243 \
     -H "Authorization: Bearer <MUTAWALLI_TOKEN>" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "masjid_id": 1,
       "name": "Mohammed Ali",
       "father_name": "Ghulam Ali",
       "unit_count": 4,
       "phone": "+91 9876543210",
       "mohalla": "Noorani Chawk",
       "notes": "House #42, near water tank"
     }'
   ```
2. Confirm status code is `200 OK` and response contains updated donor fields.
