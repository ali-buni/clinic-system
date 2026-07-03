@extends('admin.layouts.app')
@section('title', 'Activity Logs')
@section('header', 'Activity Logs')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <select name="log_name" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Log Names</option>
                @foreach($logNames as $name)
                    <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="event" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Events</option>
                @foreach($events as $event)
                    <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ $event }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" placeholder="From"
                   class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="to" value="{{ request('to') }}" placeholder="To"
                   class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..."
                   class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="col-span-2 md:col-span-4 flex gap-2">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Filter</button>
                @if(request()->hasAny(['log_name', 'event', 'from', 'to', 'search']))
                    <a href="{{ route('admin.logs.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Log Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Causer</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $log->id }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $log->log_name }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm max-w-xs truncate">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($log->event)
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $log->event }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ class_basename($log->subject_type ?? '') }} #{{ $log->subject_id }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->causer?->fname }} {{ $log->causer?->lname }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $log->created_at->format('M d, H:i') }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('admin.logs.show', $log) }}" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        No activity logs found. Make sure ActivityLogService is enabled.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
