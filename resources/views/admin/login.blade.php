<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(251,191,36,0.18), transparent 35%),
                radial-gradient(circle at bottom left, rgba(253,230,138,0.22), transparent 32%),
                #FFFDF7;
            min-height: 100vh;
            color: #111827;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 shadow-lg shadow-amber-500/25 mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-[#111827]">Admin Panel</h1>
            <p class="text-gray-500 mt-1 text-sm">WAJ Attendance System</p>
        </div>

        <div class="rounded-[28px] border border-amber-100 bg-white/80 p-6 shadow-[0_20px_60px_rgba(180,140,30,0.12)] backdrop-blur-sm">
            <form method="POST" action="{{ url('/admin/login') }}" class="space-y-5">
                @csrf

                <!-- Error Message -->
                @if (session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-600 text-sm rounded-2xl p-3 text-center">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        required
                        autocomplete="username"
                        class="w-full px-4 py-3.5 bg-[#FFFDF7] border border-amber-200 rounded-2xl text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3.5 bg-[#FFFDF7] border border-amber-200 rounded-2xl text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all"
                    >
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white font-semibold text-lg rounded-2xl shadow-lg shadow-amber-500/20 transition-all duration-200 active:scale-[0.98]"
                >
                    Masuk
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>
</body>
</html>
