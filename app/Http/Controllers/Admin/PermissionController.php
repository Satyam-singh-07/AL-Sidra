<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PermissionController extends Controller
{
    /**
     * Display users with roles
     */
    public function index()
    {
        $users = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('slug', ['user', 'member']);
            })
            ->latest()
            ->paginate(10);

        $roles = Role::whereNotIn('slug', [
            'super_admin',
            'user',
            'member',
        ])->get();

        return view('admin.permissions', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Store new user with role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);

        // Assign single role
        $user->roles()->sync([$request->role_id]);

        return back()->with('success', 'User created successfully');
    }

    /**
     * Update user and role
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|exists:users,email',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Fetch user by email
        $user = User::where('email', $request->email)->firstOrFail();

        // Update basic info
        $user->update([
            'name'  => $request->name,
        ]);

        // Sync single role
        $user->roles()->sync([$request->role_id]);

        return back()->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent deleting super admin (extra safety)
        if ($user->roles()->where('slug', 'super_admin')->exists()) {
            return back()->with('error', 'Super Admin cannot be deleted');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }

    /**
     * Toggle user status (active / blocked)
     */
    public function toggleStatus(User $user)
    {
        // Prevent blocking yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status');
        }

        // Prevent blocking super admin
        if ($user->roles()->where('slug', 'super_admin')->exists()) {
            return back()->with('error', 'Super Admin status cannot be changed');
        }

        $user->status = $user->status === 'active' ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', 'User status updated');
    }
}
