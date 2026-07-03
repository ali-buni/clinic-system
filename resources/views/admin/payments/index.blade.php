@extends('admin.layouts.app')
@section('title', 'Payment URLs')
@section('header', 'Payment URLs')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-2">
            <select name="clinic_id" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Clinics</option>
                @foreach($clinics as $id => $title)
                    <option value="{{ $id }}" {{ request('clinic_id') == $id ? 'selected' : '' }}>{{ $title }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Filter</button>
            @if(request('clinic_id') || request('status'))
                <a href="{{ route('admin.payment-urls.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Clear</a>
            @endif
        </form>
    </div>

    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clinic</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $payment->id }}</td>
                    <td class="px-4 py-3 text-sm">{{ $payment->invoice?->clinic?->title }}</td>
                    <td class="px-4 py-3 text-sm">#{{ $payment->invoice?->invoice_number }}</td>
                    <td class="px-4 py-3 text-sm font-medium">${{ number_format($payment->amount, 2) }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($payment->paid_at)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $payment->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-sm space-x-2">
                        <a href="{{ route('admin.payment-urls.show', $payment) }}" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No payment URLs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4">
        {{ $payments->links() }}
    </div>
</div>
@endsection
