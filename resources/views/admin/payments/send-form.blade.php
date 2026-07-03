@extends('admin.layouts.app')
@section('title', 'Send Payment URL')
@section('header', 'Send Payment URL to: ' . $clinic->title)

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <div class="mb-6 p-4 bg-gray-50 rounded">
        <h3 class="font-semibold">{{ $clinic->title }}</h3>
        <p class="text-sm text-gray-600">Owner: {{ $clinic->owner?->fname }} {{ $clinic->owner?->lname }} ({{ $clinic->owner?->email }})</p>
    </div>

    <form method="POST" action="{{ route('admin.clinics.send-payment.store', $clinic) }}">
        @csrf
        <div class="space-y-4">
            @if($invoices->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Invoice (optional)</label>
                <select name="invoice_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">None - create new invoice</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}" data-remaining="{{ $invoice->getRemainingBalance() }}">
                            #{{ $invoice->invoice_number }} - ${{ number_format($invoice->getRemainingBalance(), 2) }} remaining ({{ $invoice->status }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount ($) *</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       id="amount-input"
                       placeholder="Enter payment amount">
            </div>
            <div class="p-3 bg-blue-50 rounded text-sm text-blue-700">
                A Stripe Checkout payment URL will be generated and emailed to {{ $clinic->owner?->email }}.
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Generate & Send Payment URL</button>
            <a href="{{ route('admin.clinics.show', $clinic) }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Cancel</a>
        </div>
    </form>
</div>

@if($invoices->isNotEmpty())
@push('scripts')
<script>
    document.querySelector('select[name="invoice_id"]').addEventListener('change', function() {
        const remaining = this.options[this.selectedIndex].dataset.remaining;
        if (remaining) {
            document.getElementById('amount-input').value = remaining;
        }
    });
</script>
@endpush
@endif
@endsection
