<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MemberController extends Controller
{

public function index(Request $request)
{
    $activeTab = $request->get('tab', 'pending');

    $baseQuery = User::members()
        ->with(['memberProfile.category', 'memberProfile.kyc', 'memberProfile.place'])
        ->when($request->search, function ($q) use ($request) {
            $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        })
        ->when($request->category, function ($q) use ($request) {
            $q->whereHas('memberProfile', fn ($qq) =>
                $qq->where('member_category_id', $request->category)
            );
        })
        ->when($request->date, function ($q) use ($request) {
            $q->whereDate('created_at', $request->date);
        });

    $pendingMembers = (clone $baseQuery)
        ->whereHas('memberProfile', fn ($q) => $q->where('kyc_status', 'submitted'))
        ->paginate(50, ['*'], 'pending_page')
        ->withQueryString();

    $approvedMembers = (clone $baseQuery)
        ->whereHas('memberProfile', fn ($q) => $q->where('kyc_status', 'approved'))
        ->paginate(50, ['*'], 'approved_page')
        ->withQueryString();

    $rejectedMembers = (clone $baseQuery)
        ->whereHas('memberProfile', fn ($q) => $q->where('kyc_status', 'rejected'))
        ->paginate(50, ['*'], 'rejected_page')
        ->withQueryString();

    $stats = [
        'pending'  => $pendingMembers->total(),
        'approved' => $approvedMembers->total(),
        'rejected' => $rejectedMembers->total(),
        'total'    => $pendingMembers->total()
                    + $approvedMembers->total()
                    + $rejectedMembers->total(),
    ];

    $categories = \App\Models\MemberCategory::orderBy('name')->get();

    return view('admin.members', compact(
        'pendingMembers',
        'approvedMembers',
        'rejectedMembers',
        'stats',
        'activeTab',
        'categories'
    ));
}



public function kyc(User $member)
{
    $member->load([
        'memberProfile.category',
        'memberProfile.place',
        'memberProfile.kyc',
    ]);

    $kyc = $member->memberProfile->kyc;

    return response()->json([
        'member' => $member,
        'kyc' => $kyc ? array_merge(
            $kyc->toArray(),
            ['submitted_at_human' => $kyc->submitted_at?->diffForHumans()]
        ) : null,
    ]);
}


public function approveKyc(User $member)
{
    $profile = $member->memberProfile;

    if ($profile->kyc_status !== 'submitted') {
        return response()->json([
            'message' => 'KYC is not in submitted state'
        ], 422);
    }

    DB::transaction(function () use ($profile) {
        $profile->update([
            'kyc_status'        => 'approved',
            'kyc_reviewed_by'   => auth()->id(),
            'kyc_reviewed_at'   => now(),
            'rejection_reason' => null,
            'rejection_notes'  => null,
        ]);
    });

    return response()->json([
        'message' => 'KYC approved successfully'
    ]);
}

public function rejectKyc(Request $request, User $member)
{
    $request->validate([
        'reason' => 'required|string|max:100',
        'notes'  => 'nullable|string|max:500',
    ]);

    $profile = $member->memberProfile;

    if ($profile->kyc_status !== 'submitted') {
        return response()->json([
            'message' => 'KYC is not in submitted state'
        ], 422);
    }

    DB::transaction(function () use ($profile, $request) {
        $profile->update([
            'kyc_status'        => 'rejected',
            'rejection_reason'  => $request->reason,
            'rejection_notes'   => $request->notes,
            'kyc_reviewed_by'   => auth()->id(),
            'kyc_reviewed_at'   => now(),
        ]);
    });

    return response()->json([
        'message' => 'KYC rejected successfully'
    ]);
}

public function toggleStatus(User $member)
{
    $member->toggleStatus();

    return back()->with('success', 'Member status updated successfully');
}



}
