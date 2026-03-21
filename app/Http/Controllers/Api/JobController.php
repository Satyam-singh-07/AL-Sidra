<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Get masjid job categories
     */
    public function getMasjidCategories()
    {
        $categories = JobCategory::where('type', 'masjid')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Masjid job categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Get madarsa job categories
     */
    public function getMadarsaCategories()
    {
        $categories = JobCategory::where('type', 'madarsa')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Madarsa job categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * Store a new job opening (Mutvalli only)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Check if user is a member and has a profile
        $memberProfile = $user->memberProfile;
        
        if (!$memberProfile) {
            return response()->json([
                'success' => false,
                'message' => 'You must be a registered member to post a job.'
            ], 403);
        }

        // Check if user is a Mutvalli (Recruiter)
        // We check category name 'Mutvalli' (ID 4)
        if (!$memberProfile->category || $memberProfile->category->name !== 'Mutvalli') {
            return response()->json([
                'success' => false,
                'message' => 'Only a Mutvalli can post job openings.'
            ], 403);
        }

        // Check if KYC is approved (optional but usually required for recruiters)
        if ($memberProfile->kyc_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your KYC must be approved before you can post job openings.'
            ], 403);
        }

        // Validate request
        $request->validate([
            'job_category_id' => 'required|exists:job_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
        ]);

        // Create the job
        $job = Job::create([
            'user_id' => $user->id,
            'job_category_id' => $request->job_category_id,
            'place_type' => $memberProfile->place_type,
            'place_id' => $memberProfile->place_id,
            'title' => $request->title,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'salary_range' => $request->salary_range,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'status' => 'pending', // Jobs need admin approval first?
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job opening posted successfully and is pending approval.',
            'data' => $job
        ], 201);
    }
}
