@extends('admin.layouts.app')
@section('title', $clinic->title)
@section('header', $clinic->title)
@section('actions')
    <a href="{{ route('admin.clinics.send-payment', $clinic) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send Payment URL</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Clinic Info</h3>
            <dl class="grid grid-cols-2 gap-4">
                <div><dt class="text-sm text-gray-500">Title</dt><dd class="font-medium">{{ $clinic->title }}</dd></div>
                <div><dt class="text-sm text-gray-500">Phone</dt><dd class="font-medium">{{ $clinic->phone }}</dd></div>
                <div><dt class="text-sm text-gray-500">Location</dt><dd class="font-medium">{{ $clinic->location?->city }}, {{ $clinic->location?->governorate }}, {{ $clinic->location?->country }}</dd></div>
                <div><dt class="text-sm text-gray-500">Owner</dt><dd class="font-medium">{{ $clinic->owner?->fname }} {{ $clinic->owner?->lname }}</dd></div>
                <div><dt class="text-sm text-gray-500">Doctors</dt><dd class="font-medium">{{ $clinic->doctors->count() }}</dd></div>
                <div><dt class="text-sm text-gray-500">Secretaries</dt><dd class="font-medium">{{ $clinic->secretaries->count() }}</dd></div>
                <div><dt class="text-sm text-gray-500">Appointments</dt><dd class="font-medium">{{ $clinic->appointments->count() }}</dd></div>
            </dl>
        </div>

        @if($clinic->doctors->count())
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Doctors</h3>
            <ul class="divide-y divide-gray-200">
                @foreach($clinic->doctors as $doctor)
                    <li class="py-2">
                        <span class="font-medium">{{ $doctor->user?->fname }} {{ $doctor->user?->lname }}</span>
                        <span class="text-sm text-gray-500 ml-2">({{ $doctor->user?->email }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($clinic->secretaries->count())
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Secretaries</h3>
            <ul class="divide-y divide-gray-200">
                @foreach($clinic->secretaries as $secretary)
                    <li class="py-2">
                        <span class="font-medium">{{ $secretary->user?->fname }} {{ $secretary->user?->lname }}</span>
                        <span class="text-sm text-gray-500 ml-2">({{ $secretary->user?->email }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.clinics.edit', $clinic) }}" class="block w-full text-center bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Edit Clinic</a>
                <a href="{{ route('admin.clinics.send-payment', $clinic) }}" class="block w-full text-center bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Send Payment URL</a>
                <form method="POST" action="{{ route('admin.clinics.destroy', $clinic) }}" onsubmit="return confirm('Delete this clinic?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete Clinic</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
