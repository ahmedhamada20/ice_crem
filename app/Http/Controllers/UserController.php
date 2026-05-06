<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all(['name']);
        $zones = Zone::active()->get(['id', 'name']);
        return view('users.index', compact('roles', 'zones'));
    }

    public function getData(): JsonResponse
    {
        return DataTables::eloquent(User::query()->with('roles:id,name')->select('users.*'))
            ->addColumn('roles_list', fn ($u) => $u->roles->pluck('name')->join(', '))
            ->addColumn('actions', fn ($u) => "<button data-id='{$u->id}' class='btn btn-sm btn-warning btn-edit'><i class='bi bi-pencil'></i></button>
                <button data-id='{$u->id}' class='btn btn-sm btn-danger btn-delete'><i class='bi bi-trash'></i></button>")
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
            'zone_id'  => 'nullable|exists:zones,id',
            'role'     => 'required|exists:roles,name',
        ]);

        $role = $data['role']; unset($data['role']);
        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'active';

        $user = User::create($data);
        $user->assignRole($role);

        return response()->json(['success' => true, 'message' => __('Saved successfully')]);
    }

    public function edit(User $user): JsonResponse
    {
        $user->loadMissing('roles');
        return response()->json(array_merge($user->toArray(), ['role' => $user->roles->first()?->name]));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
            'zone_id'  => 'nullable|exists:zones,id',
            'role'     => 'required|exists:roles,name',
        ]);

        if (! empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);

        $role = $data['role']; unset($data['role']);
        $user->update($data);
        $user->syncRoles([$role]);

        return response()->json(['success' => true, 'message' => __('Updated successfully')]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكن حذف حسابك'], 422);
        }
        $user->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }

    public function show(User $user) { return view('users.show', compact('user')); }
    public function create() { return redirect()->route('users.index'); }
}
