# Backend Instructions: Donor Registration & Offline Donation Assignment Workflow

## Overview
This document instructs the backend agent on updating the donor application and donation assignment workflow in the Laravel backend (`AL-Sidra`).

### Problem Statement
1. **Unwanted Auto-Ledger Population**: When a user registered as a donor for a masjid via `MasjidSelectController@selectMasjid`, the backend automatically looped through all active campaigns and inserted unpaid `DonationLedger` entries for that user. This populated the masjid's Hisab-Kitab tab prematurely before any donation amount was agreed or assigned.
2. **Mohalla Requirement**: The `selectMasjid` endpoint previously required `mohalla: required_without:mohalla_id`. Donors should be able to register without choosing a mohalla (defaulting to `'General'` or null).
3. **Missing Assignment Endpoint**: The Mutawalli needs an endpoint to assign offline cash donation amounts to applied donors from the new **Donors** directory tab, which creates the ledger entry and dispatches an FCM notification to the donor.

---

## Task 1: Update `app/Http/Controllers/Api/MasjidSelectController.php`

### 1. Relax Mohalla Validation
In `selectMasjid(Request $request)`:
Make `mohalla` optional (`nullable|string`). If not provided, fallback to `'General'`.

```php
$request->validate([
    'place_id'   => 'required|exists:places,id',
    'mohalla_id' => 'nullable|exists:mohallas,id',
    'mohalla'    => 'nullable|string|max:255',
]);
```

### 2. Remove Premature Campaign Ledger Auto-Creation
Locate and **delete/comment out** the loop that was creating `DonationLedger` entries for all active campaigns:

```php
// ==============================================================
// REMOVE OR COMMENT OUT THIS SECTION:
// ==============================================================
/*
$activeCampaigns = Campaign::where('place_id', $placeId)
    ->where('status', 'active')
    ->get();

foreach ($activeCampaigns as $campaign) {
    DonationLedger::firstOrCreate(
        [
            'campaign_id' => $campaign->id,
            'user_id'     => $user->id,
        ],
        [
            'total_amount' => $campaign->is_unit_based ? $campaign->unit_amount : 0,
            'unit_count'   => 1,
            'status'       => 'unpaid',
            'mohalla'      => $mohallaName ?? 'General',
        ]
    );
}
*/
```

Now, when a user selects a masjid as a donor, only the `PlaceUser` (or `DonorPlace`) pivot record is created/linked. The user is registered as a donor of the masjid, but **no** records are placed into `donation_ledgers` until assigned by the Mutawalli.

---

## Task 2: Register API Route in `routes/api.php`

Ensure the route is registered under authenticated middleware (e.g. `auth:sanctum`):

```php
Route::middleware('auth:sanctum')->group(function () {
    // ... existing ledger routes ...
    Route::post('ledger/assign-amount', [App\Http\Controllers\Api\LedgerController::class, 'assignDonationAmount']);
});
```

*Note: The Flutter client calls `${ApiConfig.baseUrl}/ledger/assign-amount`.*

---

## Task 3: Implement `assignDonationAmount` in `app/Http/Controllers/Api/LedgerController.php`

Add the `assignDonationAmount` method to `LedgerController`:

```php
use App\Models\Campaign;
use App\Models\DonationLedger;
use App\Models\Place;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

/**
 * Assign an offline cash donation amount to an applied donor.
 * Creates/updates the campaign donation ledger and notifies the donor via FCM.
 */
public function assignDonationAmount(Request $request, FirebaseNotificationService $firebase)
{
    $validated = $request->validate([
        'campaign_id' => 'required|exists:campaigns,id',
        'user_id'     => 'required|exists:users,id',
        'amount'      => 'required|numeric|min:1',
        'unit_count'  => 'nullable|integer|min:1',
        'mohalla'     => 'nullable|string|max:255',
        'notes'       => 'nullable|string|max:1000',
        'masjid_id'   => 'nullable|exists:places,id',
    ]);

    $user = $request->user();
    $campaign = Campaign::with('place')->findOrFail($validated['campaign_id']);
    $masjidId = $campaign->place_id;

    // Authorization: Verify the requesting user is the Mutawalli / Admin of this masjid
    $isAuthorized = $user->is_super_admin 
        || $campaign->place->user_id === $user->id
        || \App\Models\MohallaMutawalli::where('place_id', $masjidId)
            ->where('user_id', $user->id)
            ->exists();

    if (!$isAuthorized) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Only the Mutawalli or assigned sub-admins can assign donations.',
        ], 403);
    }

    // Determine unit count & total amount
    $unitCount = $validated['unit_count'] ?? 1;
    $amount = (float) $validated['amount'];
    $mohalla = !empty($validated['mohalla']) ? $validated['mohalla'] : 'General';

    // Create or update the ledger record
    $ledger = DonationLedger::updateOrCreate(
        [
            'campaign_id' => $campaign->id,
            'user_id'     => $validated['user_id'],
        ],
        [
            'total_amount' => $amount,
            'unit_count'   => $unitCount,
            'mohalla'      => $mohalla,
            'notes'        => $validated['notes'] ?? null,
            'status'       => 'unpaid', // Pending offline cash collection
        ]
    );

    // Dispatch FCM push notification to the donor
    try {
        $masjidName = $campaign->place->name ?? 'Masjid';
        $title = "Donation Assigned: {$campaign->title}";
        $body = "Assalamu Alaikum. An offline cash donation of ₹" . number_format($amount, 0) . 
                " has been assigned to you for \"{$campaign->title}\" at {$masjidName}. Please pay this offline to the Mutawalli in cash.";

        $firebase->sendToUser(
            $validated['user_id'],
            $title,
            $body,
            [
                'type'        => 'donation_assigned',
                'campaign_id' => (string) $campaign->id,
                'ledger_id'   => (string) $ledger->id,
                'amount'      => (string) $amount,
            ]
        );
    } catch (\Throwable $e) {
        \Log::error('FCM donation assignment notification failed: ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => 'Donation amount successfully assigned and donor notified.',
        'data'    => $ledger->fresh(['user', 'campaign']),
    ], 200);
}
```

---

## Task 4: Verify Existing Donor Listing Endpoint

Verify that `GET /api/places/{placeId}/donors` (or `masjid-donors` endpoint used in `masjid_management_controller.dart`):
1. Returns all registered donors for that masjid from `place_user` / `places_users` table with user details:
   - `id` (User ID)
   - `name`
   - `phone`
   - `email`
   - `mohalla`
2. Does **not** require the user to have an existing `donation_ledgers` row to be returned.

---

## Verification Checklist

1. [ ] **Donor Registration**: Calling `POST /api/places/select` as a normal user adds them to the masjid's donor list with `mohalla` set to `'General'` or null, without adding rows to `donation_ledgers`.
2. [ ] **Hisab-Kitab Isolation**: Check `GET /api/campaigns/{id}` or `GET /api/ledger` for the campaign; the newly registered donor must **not** appear until an amount is assigned.
3. [ ] **Directory Visibility**: Check `GET /api/places/{placeId}/donors`; the registered donor appears in the list.
4. [ ] **Assign Amount**: Calling `POST /api/ledger/assign-amount` with valid Mutawalli authentication:
   - Creates/updates `DonationLedger` with `status: 'unpaid'`.
   - Sends FCM notification to the donor.
   - Now the donor appears in the Hisab-Kitab campaign ledger.
