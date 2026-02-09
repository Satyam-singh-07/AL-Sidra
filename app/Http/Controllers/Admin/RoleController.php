<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.roles', [
            'roles' => Role::with('modules')
                ->whereNotIn('slug', [
                    'super-admin',
                    'admin',
                    'user',
                    'member',
                ])
                ->get(),
            'modules' => config('modules'),
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'modules' => 'array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        foreach ($request->modules ?? [] as $module) {
            $role->modules()->create(['module' => $module]);
        }

        return back()->with('success', 'Role created');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->slug === 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'modules' => 'array',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        $role->modules()->delete();

        foreach ($request->modules ?? [] as $module) {
            $role->modules()->create(['module' => $module]);
        }

        return back()->with('success', 'Role updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->slug === 'admin') {
            abort(403);
        }

        $role->delete();

        return back()->with('success', 'Role deleted');
    }
}
