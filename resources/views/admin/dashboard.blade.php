@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Clinics</div>
        <div class="text-2xl font-bold text-gray-800">{{ $totalClinics }}</div>
        <div class="text-xs text-gray-400">{{ $activeClinics }} active</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Users</div>
        <div class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Appointments</div>
        <div class="text-2xl font-bold text-gray-800">{{ $totalAppointments }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Revenue</div>
        <div class="text-2xl font-bold text-gray-800">${{ number_format($totalRevenue, 2) }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Users by Role</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Owners</span>
                <span class="font-semibold">{{ $usersByRole->owners ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Doctors</span>
                <span class="font-semibold">{{ $usersByRole->doctors ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Secretaries</span>
                <span class="font-semibold">{{ $usersByRole->secretaries ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Patients</span>
                <span class="font-semibold">{{ $usersByRole->patients ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Overview</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Total Payments</span>
                <span class="font-semibold">{{ $totalPayments }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Pending Payments</span>
                <span class="font-semibold text-yellow-600">{{ $pendingPayments }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
