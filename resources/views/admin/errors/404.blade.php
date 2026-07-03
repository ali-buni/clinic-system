<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800">404</h1>
        <p class="mt-4 text-xl text-gray-600">Page not found</p>
        <p class="mt-2 text-gray-500">The page you're looking for doesn't exist or has been moved.</p>
        <a href="{{ route('admin.dashboard') }}" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Back to Dashboard</a>
    </div>
</body>
</html>
