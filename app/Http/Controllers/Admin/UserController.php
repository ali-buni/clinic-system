<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Jobs\LogActivityJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = ['owner', 'doctor', 'secretary', 'patient'];

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = ['owner', 'doctor', 'secretary', 'patient'];

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->assignRole($role);

        return redirect()->route('admin.users.show', $user)->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles', 'clinicOwner', 'doctorProfile', 'patientProfile', 'secretaryProfile');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = ['owner', 'doctor', 'secretary', 'patient'];
        $currentRole = $user->roles->first()?->name;

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $role = $data['role'] ?? null;
        unset($data['role']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        if ($role && $user->roles->first()?->name !== $role) {
            $oldRole = $user->roles->first()?->name;
            $user->syncRoles([$role]);

            LogActivityJob::dispatch(
                'user',
                'changed user role',
                get_class($user),
                $user->id,
                auth()->user(),
                ['old_role' => $oldRole, 'new_role' => $role],
                'role_changed'
            );
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function restore(User $user)
    {
        $user->restore();

        return redirect()->route('admin.users.show', $user)->with('success', 'User restored successfully.');
    }
}
