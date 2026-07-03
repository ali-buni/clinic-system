@extends('admin.layouts.app')
@section('title', 'Payment URL Generated')
@section('header', 'Payment URL Generated')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <div class="mb-4 p-4 bg-green-50 border border-green-400 text-green-700 rounded">
        Payment URL generated successfully and sent to {{ $clinic->owner?->email }}
    </div>

    <dl class="space-y-4">
        <div>
            <dt class="text-sm text-gray-500">Clinic</dt>
            <dd class="font-medium">{{ $clinic->title }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Invoice</dt>
            <dd class="font-medium">#{{ $payment->invoice?->invoice_number }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Amount</dt>
            <dd class="font-medium">${{ number_format($payment->amount, 2) }}</dd>
        </div>
        <div>
            <dt class="block text-sm font-medium text-gray-700 mb-1">Payment URL</dt>
            <dd>
                <div class="flex gap-2">
                    <input type="text" value="{{ $paymentUrl }}" readonly id="payment-url"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                    <button onclick="navigator.clipboard.writeText(document.getElementById('payment-url').value)"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Copy</button>
                </div>
            </dd>
        </div>
    </dl>

    <div class="mt-6 flex gap-2">
        <a href="{{ route('admin.payment-urls.show', $payment) }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">View Details</a>
        <a href="{{ route('admin.clinics.show', $clinic) }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Back to Clinic</a>
    </div>
</div>
@endsection
