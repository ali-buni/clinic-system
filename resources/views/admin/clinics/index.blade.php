@extends('admin.layouts.app')
@section('title', 'Clinics')
@section('header', 'Clinics')
@section('actions')
<a href="{{ route('admin.clinics.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ New Clinic</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search clinics..."
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.clinics.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Clear</a>
            @endif
        </form>
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($clinics as $clinic)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">{{ $clinic->id }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ $clinic->title }}</td>
                <td class="px-4 py-3 text-sm">{{ $clinic->phone }}</td>
                <td class="px-4 py-3 text-sm">{{ $clinic->location}}</td>
                <td class="px-4 py-3 text-sm">{{ $clinic->owner?->fname }} {{ $clinic->owner?->lname }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $clinic->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-3 text-sm space-x-2">
                    <a href="{{ route('admin.clinics.show', $clinic) }}" class="text-blue-600 hover:underline">View</a>
                    <a href="{{ route('admin.clinics.edit', $clinic) }}" class="text-yellow-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.clinics.destroy', $clinic) }}" class="inline" onsubmit="return confirm('Delete this clinic?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No clinics found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4">
        {{ $clinics->links() }}
    </div>
</div>
@endsection