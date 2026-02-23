<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin-panel.index', ['roles' => $roles]);
    }

    public function create()
    {
        return view('admin-panel.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
        ]);

        Role::create($data);
        return redirect()->route('roles.index')->with('success', 'Role dibuat.');
    }

    public function edit(Role $role)
    {
        return view('admin-panel.edit', ['role' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'display_name' => 'nullable|string|max:255',
        ]);

        $role->update($data);
        return redirect()->route('roles.index')->with('success', 'Role diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role dihapus.');
    }
}
