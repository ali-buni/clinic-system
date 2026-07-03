<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Clinic System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-gray-800">Clinic System</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/admin/login" class="text-sm text-gray-600 hover:text-gray-900">Admin Panel</a>
                <a href="/docs" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">API Docs</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-6 py-20 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Laravel Clinic System</h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto mb-8">
                A bilingual (AR/EN) clinic management platform with AI-powered features, real-time analytics, and Stripe payment integration.
            </p>
            <div class="flex justify-center gap-4">
                <a href="/admin/login" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">Open Admin Panel</a>
                <a href="#features" class="border border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Tech Stack -->
    <section class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">Laravel 11</div>
                <div class="text-sm text-gray-500 mt-1">Backend Framework</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">REST API</div>
                <div class="text-sm text-gray-500 mt-1">88+ Endpoints</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">AI Powered</div>
                <div class="text-sm text-gray-500 mt-1">Multi-Provider Router</div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">Stripe</div>
                <div class="text-sm text-gray-500 mt-1">Payment Integration</div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-12">Platform Features</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Identity & Auth -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Identity & Authentication</h3>
                    <p class="text-sm text-gray-600">Email/password login, Google OAuth, phone/email verification, 2FA-style code flow, Sanctum token auth.</p>
                </div>

                <!-- Clinic Management -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Clinic Management</h3>
                    <p class="text-sm text-gray-600">Multi-clinic support, rooms, doctors, secretaries, work hours, schedule overrides. Each clinic is tenant-isolated.</p>
                </div>

                <!-- Scheduling -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Scheduling & Booking</h3>
                    <p class="text-sm text-gray-600">Slot generation, availability checking, appointment lifecycle (book, confirm, complete, cancel, no-show).</p>
                </div>

                <!-- Patient Records -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Clinical Records</h3>
                    <p class="text-sm text-gray-600">Patient records with ICD-10 disease linkage, prescriptions, medicine catalog. All PHI fields encrypted with CipherSweet.</p>
                </div>

                <!-- Billing -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Billing & Payments</h3>
                    <p class="text-sm text-gray-600">Invoices, partial payments, Stripe Checkout integration, payment URL generation, webhook handling.</p>
                </div>

                <!-- AI Services -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">AI-Powered Features</h3>
                    <p class="text-sm text-gray-600">Specialty matching (200+ keywords + AI fallback), appointment assistant, medical report summarization, patient chatbot.</p>
                </div>

                <!-- Analytics -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">9-Dimensional Analytics</h3>
                    <p class="text-sm text-gray-600">Operational, financial, patient, medical, predictive analytics, NLP querying, health scoring, daily snapshots.</p>
                </div>

                <!-- Admin Panel -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Admin Panel</h3>
                    <p class="text-sm text-gray-600">Platform-level management: clinics, users, roles. Payment URL generation, activity logs, structured log viewer.</p>
                </div>

                <!-- Multi-language -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Bilingual (AR/EN)</h3>
                    <p class="text-sm text-gray-600">Full Arabic and English support across all models, seeders, and user-facing content.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Roles -->
    <section class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-bold text-center mb-12">Role-Based Access Control</h2>
        <div class="grid md:grid-cols-5 gap-6">
            <div class="bg-white border rounded-xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-red-600 font-bold text-lg">A</span>
                </div>
                <h4 class="font-semibold">Admin</h4>
                <p class="text-xs text-gray-500 mt-1">Platform-wide management, all clinics, all users</p>
            </div>
            <div class="bg-white border rounded-xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-blue-600 font-bold text-lg">O</span>
                </div>
                <h4 class="font-semibold">Owner</h4>
                <p class="text-xs text-gray-500 mt-1">One clinic, doctors, patients, finances</p>
            </div>
            <div class="bg-white border rounded-xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-green-600 font-bold text-lg">D</span>
                </div>
                <h4 class="font-semibold">Doctor</h4>
                <p class="text-xs text-gray-500 mt-1">Patients, appointments, records, schedules</p>
            </div>
            <div class="bg-white border rounded-xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-yellow-600 font-bold text-lg">S</span>
                </div>
                <h4 class="font-semibold">Secretary</h4>
                <p class="text-xs text-gray-500 mt-1">Front desk, patients, appointments</p>
            </div>
            <div class="bg-white border rounded-xl p-6 text-center shadow-sm">
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-purple-600 font-bold text-lg">P</span>
                </div>
                <h4 class="font-semibold">Patient</h4>
                <p class="text-xs text-gray-500 mt-1">Own appointments, records, invoices</p>
            </div>
        </div>
    </section>

    <!-- Quick Start -->
    <section class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-12">Quick Start</h2>
            <div class="max-w-2xl mx-auto space-y-4">
                <div class="bg-gray-800 rounded-lg p-4 font-mono text-sm">
                    <div class="text-gray-400"># Clone and install</div>
                    <div class="text-green-400">git clone &lt;repo-url&gt;</div>
                    <div class="text-green-400">cd clinic-system</div>
                    <div class="text-green-400">composer install</div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 font-mono text-sm">
                    <div class="text-gray-400"># Setup environment</div>
                    <div class="text-green-400">cp .env.example .env</div>
                    <div class="text-green-400">php artisan key:generate</div>
                    <div class="text-green-400">php artisan migrate --seed</div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 font-mono text-sm">
                    <div class="text-gray-400"># Start the server</div>
                    <div class="text-green-400">php artisan serve</div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 font-mono text-sm">
                    <div class="text-gray-400"># Admin Panel</div>
                    <div class="text-yellow-400">URL:      http://localhost:8000/admin/login</div>
                    <div class="text-yellow-400">Email:    admin@clinic.com</div>
                    <div class="text-yellow-400">Password: password</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t py-8">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm text-gray-500">
            <p>Laravel Clinic System &mdash; Built with Laravel {{ Illuminate\Foundation\Application::VERSION }}</p>
            <p class="mt-1">
                <a href="/admin/login" class="text-blue-600 hover:underline">Admin Panel</a>
                &middot;
                <a href="/docs" class="text-blue-600 hover:underline">API Docs</a>
            </p>
        </div>
    </footer>

</body>
</html>
