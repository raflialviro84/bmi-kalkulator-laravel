<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Role;
class AdminController extends Controller
{
    public function index()
    {
        // provide roles list to the admin dashboard (uses same view)
        $roles = Role::orderBy('name')->get();
        return view('admin-panel.index', ['roles' => $roles]);
    }
}
