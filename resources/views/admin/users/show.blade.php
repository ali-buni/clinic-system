@extends('admin.layouts.app')
@section('title', $user->fname . ' ' . $user->lname)
@section('header', $user->fname . ' ' . $user->lname)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">User Info</h3>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">Full Name</dt>
                    <dd class="font-medium">{{ $user->fname }} {{ $user->lname }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Phone</dt>
                    <dd class="font-medium">{{ $user->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Role</dt>
                    <dd class="font-medium">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            {{ $user->roles->first()?->name ?? 'None' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Gender</dt>
                    <dd class="font-medium">{{ $user->gender ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Date of Birth</dt>
                    <dd class="font-medium">{{ $user->dob ? date('M d, Y', strtotime($user->dob)) : 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Created At</dt>
                    <dd class="font-medium">{{ $user->created_at ? date('M d, Y', strtotime($user->created_at)) : 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="block w-full text-center bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Edit User</a>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete User</button>
                </form>
            </div>
        </div>

        @if($user->clinicOwner)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Clinic</h3>
            <a href="{{ route('admin.clinics.show', $user->clinicOwner) }}" class="text-blue-600 hover:underline">{{ $user->clinicOwner->title }}</a>
        </div>
        @endif
    </div>
</div>
@endsection