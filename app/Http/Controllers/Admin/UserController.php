<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{

public function index(Request $request)
{
    $baseQuery = User::users();

    $stats = [
        'total' => (clone $baseQuery)->count(),
        'today' => (clone $baseQuery)->whereDate('created_at', now())->count(),
        'month' => (clone $baseQuery)->whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->count(),
        'year' => (clone $baseQuery)->whereYear('created_at', now()->year)->count(),
    ];

    $usersQuery = clone $baseQuery;

    if ($request->filled('search')) {
        $usersQuery->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('phone', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('date')) {
        $usersQuery->whereDate('created_at', $request->date);
    }

    if ($request->sort === 'oldest') {
        $usersQuery->oldest();
    } else {
        $usersQuery->latest();
    }

    $users = $usersQuery->paginate(50)->withQueryString();

    return view('admin.users', compact('users', 'stats'));
}

public function edit(User $user)
{
    return view('admin.users-edit', compact('user'));
}

public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
        'status' => 'required|in:active,blocked',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($request->hasFile('profile_picture')) {
        if ($user->profile_picture) {
            \Storage::disk('public')->delete($user->profile_picture);
        }
        $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
    }

    $user->update($validated);

    return redirect()->route('users.index')->with('success', 'User profile updated successfully');
}

public function toggleStatus(User $user)

{
    $user->toggleStatus();

    return back()->with('success', 'User status updated successfully');
}
}
