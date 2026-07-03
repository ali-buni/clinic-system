@extends('admin.layouts.app')
@section('title', 'Activity Log Detail')
@section('header', 'Activity Log #' . $log->id)

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <dl class="grid grid-cols-2 gap-4">
        <div><dt class="text-sm text-gray-500">Log Name</dt><dd class="font-medium">{{ $log->log_name }}</dd></div>
        <div><dt class="text-sm text-gray-500">Event</dt><dd class="font-medium">{{ $log->event ?? 'N/A' }}</dd></div>
        <div><dt class="text-sm text-gray-500">Description</dt><dd class="font-medium">{{ $log->description }}</dd></div>
        <div><dt class="text-sm text-gray-500">Subject</dt><dd class="font-medium">{{ class_basename($log->subject_type ?? '') }} #{{ $log->subject_id }}</dd></div>
        <div><dt class="text-sm text-gray-500">Causer</dt><dd class="font-medium">{{ $log->causer?->fname }} {{ $log->causer?->lname }} (ID: {{ $log->causer_id }})</dd></div>
        <div><dt class="text-sm text-gray-500">Created At</dt><dd class="font-medium">{{ $log->created_at->format('M d, Y H:i:s') }}</dd></div>
    </dl>

    <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Properties</h3>
        <pre class="bg-gray-50 p-4 rounded text-sm overflow-x-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.logs.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Back to List</a>
    </div>
</div>
@endsection
