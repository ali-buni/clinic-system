@extends('admin.layouts.app')
@section('title', 'Withdrawal Details')
@section('header', 'Withdrawal #' . $withdrawal->id)

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <dl class="grid grid-cols-2 gap-4">
        <div><dt class="text-sm text-gray-500">Withdrawal ID</dt><dd class="font-medium">{{ $withdrawal->id }}</dd></div>
        <div><dt class="text-sm text-gray-500">Amount</dt><dd class="font-medium">${{ number_format($withdrawal->amount, 2) }}</dd></div>
        <div><dt class="text-sm text-gray-500">Doctor</dt><dd class="font-medium">{{ $withdrawal->doctor?->user?->fname }} {{ $withdrawal->doctor?->user?->lname }}</dd></div>
        <div><dt class="text-sm text-gray-500">Doctor Email</dt><dd class="font-medium">{{ $withdrawal->doctor?->user?->email }}</dd></div>
        <div>
            <dt class="text-sm text-gray-500">Status</dt>
            <dd class="font-medium">
                @switch($withdrawal->status->value)
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
            </dd>
        </div>
        <div><dt class="text-sm text-gray-500">Stripe Account</dt><dd class="font-mono text-xs break-all">{{ $withdrawal->stripe_connected_account_id }}</dd></div>
        @if($withdrawal->stripe_transfer_id)
            <div class="col-span-2"><dt class="text-sm text-gray-500">Stripe Transfer ID</dt><dd class="font-mono text-xs break-all">{{ $withdrawal->stripe_transfer_id }}</dd></div>
        @endif
        @if($withdrawal->approvedBy)
            <div><dt class="text-sm text-gray-500">Approved By</dt><dd class="font-medium">{{ $withdrawal->approvedBy?->fname }} {{ $withdrawal->approvedBy?->lname }}</dd></div>
        @endif
        @if($withdrawal->approved_at)
            <div><dt class="text-sm text-gray-500">Approved At</dt><dd class="font-medium">{{ $withdrawal->approved_at->format('M d, Y H:i') }}</dd></div>
        @endif
        @if($withdrawal->rejection_reason)
            <div class="col-span-2"><dt class="text-sm text-gray-500">Rejection Reason</dt><dd class="font-medium text-red-600">{{ $withdrawal->rejection_reason }}</dd></div>
        @endif
        @if($withdrawal->processed_at)
            <div><dt class="text-sm text-gray-500">Processed At</dt><dd class="font-medium">{{ $withdrawal->processed_at->format('M d, Y H:i') }}</dd></div>
        @endif
        <div><dt class="text-sm text-gray-500">Requested At</dt><dd class="font-medium">{{ $withdrawal->created_at->format('M d, Y H:i') }}</dd></div>
    </dl>

    @if($withdrawal->status->value === 'pending')
        <div class="mt-6 border-t pt-4">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Actions</h3>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" onsubmit="return confirm('Are you sure you want to approve this withdrawal of ${{ number_format($withdrawal->amount, 2) }}?')">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Approve</button>
                </form>
                <button type="button" onclick="document.getElementById('reject-form').classList.toggle('hidden')" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Reject</button>
            </div>
            <form id="reject-form" method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" class="mt-4 hidden">
                @csrf
                <div class="mb-3">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Enter reason for rejection..."></textarea>
                </div>
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Confirm Rejection</button>
            </form>
        </div>
    @endif

    <div class="mt-6 flex gap-2">
        <a href="{{ route('admin.withdrawals.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Back to List</a>
    </div>

    @if($withdrawal->doctor)
    <div class="mt-6 border-t pt-4">
        <h3 class="text-sm font-medium text-gray-700 mb-3">Link Stripe Account</h3>
        <form id="link-stripe-form" class="flex gap-2 items-end">
            @csrf
            <div class="flex-1">
                <label for="stripe_account_id" class="block text-sm text-gray-500 mb-1">Stripe Account ID</label>
                <input type="text" name="stripe_account_id" id="stripe_account_id" required pattern="acct_.*"
                       value="{{ $withdrawal->doctor->stripe_connected_account_id ?? '' }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                       placeholder="acct_...">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 whitespace-nowrap">Save</button>
        </form>
        <p id="link-stripe-message" class="mt-2 text-sm hidden"></p>
    </div>
    @endif
</div>

<script>
document.getElementById('link-stripe-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const msg = document.getElementById('link-stripe-message');
    const accountId = document.getElementById('stripe_account_id').value;

    try {
        const response = await fetch('{{ route("admin.doctors.link-stripe", $withdrawal->doctor->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ stripe_account_id: accountId }),
        });
        const data = await response.json();
        msg.textContent = data.message || (response.ok ? 'Stripe account linked successfully.' : 'Failed to link account.');
        msg.className = 'mt-2 text-sm ' + (response.ok ? 'text-green-600' : 'text-red-600');
    } catch {
        msg.textContent = 'Request failed.';
        msg.className = 'mt-2 text-sm text-red-600';
    }
    msg.classList.remove('hidden');
});
</script>
@endsection
