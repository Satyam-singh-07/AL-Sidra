<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberKycRequest;
use App\Models\MemberKycDocument;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function store(MemberKycRequest $request)
    {
        $user = $request->user();

        $memberProfile = $user->memberProfile;

        if (!$memberProfile) {
            return response()->json([
                'message' => 'Member profile not found'
            ], 404);
        }

        $kyc = MemberKycDocument::updateOrCreate(
            ['member_profile_id' => $memberProfile->id],
            [
                'institute_name'       => $request->institute_name,
                'degree_complete_year' => $request->degree_complete_year,
            ]
        );

        if ($request->hasFile('degree_photo')) {
            $kyc->degree_photo_path =
                $request->file('degree_photo')->store('kyc/degree', 'public');
        }

        if ($request->hasFile('aadhaar_front')) {
            $kyc->aadhaar_front_path =
                $request->file('aadhaar_front')->store('kyc/aadhaar', 'public');
        }

        if ($request->hasFile('aadhaar_back')) {
            $kyc->aadhaar_back_path =
                $request->file('aadhaar_back')->store('kyc/aadhaar', 'public');
        }

        $kyc->save();

        $memberProfile->update([
            'kyc_status' => 'partial'
        ]);

        return response()->json([
            'message' => 'KYC data saved successfully',
            'kyc'     => $kyc
        ]);
    }

    public function submit(Request $request)
    {
        $user = $request->user();
        $memberProfile = $user->memberProfile;

        if (!$memberProfile) {
            return response()->json([
                'message' => 'Member profile not found'
            ], 404);
        }

        $kyc = $memberProfile->kyc;

        if (!$kyc) {
            return response()->json([
                'message' => 'KYC data not found'
            ], 422);
        }

        if (
            !$kyc->aadhaar_front_path ||
            !$kyc->aadhaar_back_path
        ) {
            return response()->json([
                'message' => 'Please upload Aadhaar documents before submitting'
            ], 422);
        }

        $kyc->update([
            'submitted_at' => now()
        ]);

        $memberProfile->update([
            'kyc_status' => 'submitted'
        ]);

        return response()->json([
            'message' => 'KYC submitted for approval'
        ]);
    }

}
