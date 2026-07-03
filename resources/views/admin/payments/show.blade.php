@extends('admin.layouts.app')
@section('title', 'Payment Details')
@section('header', 'Payment #' . $payment->id)

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <dl class="grid grid-cols-2 gap-4">
        <div><dt class="text-sm text-gray-500">Payment ID</dt><dd class="font-medium">{{ $payment->id }}</dd></div>
        <div><dt class="text-sm text-gray-500">Amount</dt><dd class="font-medium">${{ number_format($payment->amount, 2) }}</dd></div>
        <div><dt class="text-sm text-gray-500">Invoice</dt><dd class="font-medium">#{{ $payment->invoice?->invoice_number }}</dd></div>
        <div><dt class="text-sm text-gray-500">Clinic</dt><dd class="font-medium">{{ $payment->invoice?->clinic?->title }}</dd></div>
        <div><dt class="text-sm text-gray-500">Payment Method</dt><dd class="font-medium">{{ $payment->paymentMethod?->en_name }}</dd></div>
        <div><dt class="text-sm text-gray-500">Status</dt><dd class="font-medium">
            @if($payment->paid_at)
                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
            @else
                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
            @endif
        </dd></div>
        @if($payment->stripe_session_id)
        <div class="col-span-2"><dt class="text-sm text-gray-500">Stripe Session ID</dt><dd class="font-mono text-xs break-all">{{ $payment->stripe_session_id }}</dd></div>
        @endif
        <div><dt class="text-sm text-gray-500">Created At</dt><dd class="font-medium">{{ $payment->created_at->format('M d, Y H:i') }}</dd></div>
    </dl>

    <div class="mt-6 flex gap-2">
        @if($payment->invoice?->clinic)
            <a href="{{ route('admin.clinics.send-payment', $payment->invoice->clinic) }}" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Resend Payment URL</a>
        @endif
        <a href="{{ route('admin.payment-urls.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">Back to List</a>
    </div>
</div>
@endsection
