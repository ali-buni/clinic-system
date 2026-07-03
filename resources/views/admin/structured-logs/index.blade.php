@extends('admin.layouts.app')
@section('title', 'Structured Logs')
@section('header', 'Structured Logs')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">File Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Modified</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($files as $file)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium">{{ $file['date'] }}</td>
                    <td class="px-4 py-3 text-sm font-mono">{{ $file['name'] }}</td>
                    <td class="px-4 py-3 text-sm">{{ number_format($file['size'] / 1024, 1) }} KB</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($file['modified'])->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm space-x-2">
                        <a href="{{ route('admin.structured-logs.show', $file['date']) }}" class="text-blue-600 hover:underline">View</a>
                        <a href="{{ route('admin.structured-logs.download', $file['date']) }}" class="text-green-600 hover:underline">Download</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No structured log files found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
