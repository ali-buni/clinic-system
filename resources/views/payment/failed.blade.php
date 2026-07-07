<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8 text-center">
        <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
        <p class="text-gray-500 mb-6">Your payment could not be processed.</p>

        @if($payment)
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Invoice</span>
                    <span class="font-medium text-gray-900">#{{ $payment->invoice?->invoice_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Amount</span>
                    <span class="font-medium text-gray-900">${{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">Pending</span>
                </div>
            </div>
        @endif

        <p class="text-sm text-gray-400">Please try again or contact support for assistance.</p>
    </div>
</body>
</html>
