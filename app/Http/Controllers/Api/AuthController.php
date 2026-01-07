<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberSignupRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function userSignup(Request $request, OtpService $otpService)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        $otp = $otpService->generate($request->phone);

        return response()->json([
            'message' => 'OTP sent successfully',
            'otp'     => $otp
        ]);
    }

    public function verifyUserOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'otp'   => 'required|string',
        ]);

        if (!$otpService->verify($request->phone, $request->otp)) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => $request->name]
        );

        if ($user->wasRecentlyCreated === false && $user->name !== $request->name) {
            $user->update(['name' => $request->name]);
        }

        DB::table('role_user')->insertOrIgnore([
            'user_id' => $user->id,
            'role_id' => DB::table('roles')->where('slug', 'user')->value('id'),
        ]);
    
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Signup successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone,
            ]
        ]);
    }

    public function sendMemberOtp(MemberSignupRequest $request, OtpService $otpService)
    {
        $otp = $otpService->generate($request->phone);

        return response()->json([
            'message' => 'OTP sent successfully',
            'otp'     => $otp,
        ]);
    }

    public function verifyOtpAndCreateMember(
        MemberSignupRequest $request,
        OtpService $otpService
    ) {
        if (!$otpService->verify($request->phone, $request->otp)) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        return DB::transaction(function () use ($request) {

            // 1️⃣ Create or fetch user
            $user = \App\Models\User::firstOrCreate(
                ['phone' => $request->phone],
                ['name' => $request->name]
            );

            if ($user->name !== $request->name) {
                $user->update(['name' => $request->name]);
            }

            // 2️⃣ Prevent duplicate member
            if ($user->memberProfile) {
                return response()->json([
                    'message' => 'Member already exists'
                ], 409);
            }

            // 3️⃣ Validate polymorphic place
            if (
                $request->place_type === 'masjid' &&
                !\App\Models\Masjid::where('id', $request->place_id)->exists()
            ) {
                return response()->json(['message' => 'Invalid masjid'], 422);
            }

            if (
                $request->place_type === 'madarsa' &&
                !\App\Models\Madarsa::where('id', $request->place_id)->exists()
            ) {
                return response()->json(['message' => 'Invalid madarsa'], 422);
            }

            // 4️⃣ Create member profile
            $memberProfile = \App\Models\MemberProfile::create([
                'user_id'            => $user->id,
                'member_category_id' => $request->member_category_id,
                'place_type'         => $request->place_type,
                'place_id'           => $request->place_id,
                'kyc_status'         => 'not_started',
            ]);

            // 5️⃣ Optional KYC
            $kycData = [
                'member_profile_id'    => $memberProfile->id,
                'institute_name'       => $request->institute_name,
                'degree_complete_year' => $request->degree_complete_year,
            ];

            if ($request->hasFile('degree_photo')) {
                $kycData['degree_photo_path'] =
                    $request->file('degree_photo')->store('kyc/degree', 'public');
            }

            if ($request->hasFile('aadhaar_front')) {
                $kycData['aadhaar_front_path'] =
                    $request->file('aadhaar_front')->store('kyc/aadhaar', 'public');
            }

            if ($request->hasFile('aadhaar_back')) {
                $kycData['aadhaar_back_path'] =
                    $request->file('aadhaar_back')->store('kyc/aadhaar', 'public');
            }

            if (
                $request->filled('institute_name') ||
                $request->filled('degree_complete_year') ||
                $request->hasFile('degree_photo') ||
                $request->hasFile('aadhaar_front') ||
                $request->hasFile('aadhaar_back')
            ) {
                \App\Models\MemberKycDocument::create($kycData);
            }

            // 6️⃣ ROLE ASSIGNMENT (QUERY-BASED, CONSISTENT)

            $memberRoleId = DB::table('roles')->where('slug', 'member')->value('id');

            DB::table('role_user')->insertOrIgnore([
                ['user_id' => $user->id, 'role_id' => $memberRoleId],
            ]);

            // 7️⃣ Create token ONLY after everything succeeds
            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'message' => 'Member signup successful',
                'token'   => $token,
                // 'member'  => $memberProfile
            ], 201);
        });
    }

    public function userSendLoginOtp(Request $request, OtpService $otpService)
    {
        $request->validate(['phone' => 'required|string|exists:users,phone']);
        $otp = $otpService->generate($request->phone);

        return response()->json([
            'message' => 'OTP send successfully',
            'otp' => $otp
        ]);
    }

    public function userVerifyOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'otp'   => 'required|string'
        ]);

        if (!$otpService->verify($request->phone, $request->otp)) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }
}
