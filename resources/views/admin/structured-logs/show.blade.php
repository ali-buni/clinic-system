@extends('admin.layouts.app')
@section('title', 'Structured Log - ' . $date)
@section('header', 'Structured Log: ' . $date)

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-2">
            <select name="level" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Levels</option>
                @foreach($levels as $level)
                    <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search messages..."
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Filter</button>
            @if(request('level') || request('search'))
                <a href="{{ route('admin.structured-logs.show', $date) }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Clear</a>
            @endif
        </form>
    </div>

    <div class="p-4 text-sm text-gray-500">
        {{ number_format($total) }} entries
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-48">Time</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">Level</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Context</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($entries as $entry)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs font-mono text-gray-500">
                        {{ \Carbon\Carbon::parse($entry['datetime'])->format('H:i:s.u') }}
                    </td>
                    <td class="px-4 py-3 text-xs">
                        @if(($entry['level_name'] ?? '') === 'ERROR')
                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-800">ERROR</span>
                        @elseif(($entry['level_name'] ?? '') === 'WARNING')
                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">WARNING</span>
                        @else
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">INFO</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $entry['message'] ?? '' }}</td>
                    <td class="px-4 py-3 text-xs font-mono max-w-md truncate">
                        @if(!empty($entry['context']))
                            {{ json_encode($entry['context'], JSON_UNESCAPED_UNICODE) }}
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">No log entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4 flex justify-between items-center">
        <a href="{{ route('admin.structured-logs.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Back to Files</a>
        <div class="flex gap-1">
            @php
                $currentPage = (int) request('page', 1);
                $lastPage = (int) ceil($total / 50);
            @endphp
            @if($currentPage > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="px-3 py-1 border rounded hover:bg-gray-50">Prev</a>
            @endif
            <span class="px-3 py-1">Page {{ $currentPage }} of {{ $lastPage }}</span>
            @if($currentPage < $lastPage)
                <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="px-3 py-1 border rounded hover:bg-gray-50">Next</a>
            @endif
        </div>
    </div>
</div>
@endsection
