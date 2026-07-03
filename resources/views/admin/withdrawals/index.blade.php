@extends('admin.layouts.app')
@section('title', 'Doctor Withdrawals')
@section('header', 'Doctor Withdrawals')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-2">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Filter</button>
            @if(request('status'))
                <a href="{{ route('admin.withdrawals.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Clear</a>
            @endif
        </form>
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($withdrawals as $withdrawal)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $withdrawal->id }}</td>
                    <td class="px-4 py-3 text-sm">
                        {{ $withdrawal->doctor?->user?->fname }} {{ $withdrawal->doctor?->user?->lname }}
                    </td>
                    <td class="px-4 py-3 text-sm font-medium">${{ number_format($withdrawal->amount, 2) }}</td>
                    <td class="px-4 py-3 text-sm">
                        @switch($withdrawal->status)
                            @case('pending')
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @break
                            @case('approved')
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Approved</span>
                                @break
                            @case('rejected')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>
                                @break
                            @case('completed')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                                @break
                            @case('failed')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Failed</span>
                                @break
                        @endswitch
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm space-x-2">
                        <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No withdrawal requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4">
        {{ $withdrawals->links() }}
    </div>
</div>
@endsection
