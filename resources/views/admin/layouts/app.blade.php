<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-link.active { background-color: #1e40af; color: white; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 text-gray-300 flex flex-col">
            <div class="p-4 text-xl font-bold text-white border-b border-gray-700">
                Clinic Admin
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.clinics.index') }}" class="sidebar-link block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.clinics.*') ? 'active' : '' }}">Clinics</a>
                <a href="{{ route('admin.users.index') }}" class="sidebar-link block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
                <a href="{{ route('admin.payment-urls.index') }}" class="sidebar-link block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.payment-urls.*') ? 'active' : '' }}">Payment URLs</a>
                <a href="{{ route('admin.logs.index') }}" class="sidebar-link block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">Activity Logs</a>
                <a href="{{ route('admin.structured-logs.index') }}" class="sidebar-link block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.structured-logs.*') ? 'active' : '' }}">Structured Logs</a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="text-sm text-gray-400">{{ auth()->user()->fname }} {{ auth()->user()->lname }}</div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="mt-2 text-sm text-red-400 hover:text-red-300">Logout</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow p-4 flex justify-between items-center">
                <h1 class="text-lg font-semibold text-gray-800">@yield('header', 'Dashboard')</h1>
                @yield('actions')
            </header>

            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
