<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    @include('partials.base-url-meta')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(251,191,36,0.20), transparent 38%),
                radial-gradient(circle at bottom left, rgba(253,230,138,0.20), transparent 32%),
                radial-gradient(circle at 50% 0%, rgba(254,243,199,0.35), transparent 45%),
                #FFFDF7;
            min-height: 100vh;
            color: #111827;
        }
    </style>
</head>
<body class="min-h-screen px-4 py-5">
    <div class="max-w-lg mx-auto pb-28">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#111827]">Dashboard</h1>
                <p class="text-gray-500 text-sm mt-1" id="employeeName">Selamat datang</p>
            </div>
            <button id="logoutBtn" class="px-4 py-2 bg-white/80 hover:bg-white text-gray-700 text-sm rounded-2xl border border-amber-200 shadow-sm transition-all">
                Logout
            </button>
        </div>

        <div class="soft-card rounded-[28px] p-6 sm:p-7 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-amber-300 to-amber-500 shadow-[0_10px_25px_rgba(251,191,36,0.25)] mb-4">
                <svg class="w-10 h-10 text-[#111827]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-[#111827] mb-1" id="displayName">Karyawan</h2>
            <p class="text-gray-500 text-sm" id="displayPin">PIN: -</p>
            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-[#FFF7D6] border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span class="text-amber-700 text-sm font-medium">Wajah terdaftar</span>
            </div>
        </div>

        <a href="{{ url('/absen') }}" id="absenLink" class="block mt-4 w-full h-[54px] bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#111827] font-bold text-base text-center rounded-2xl shadow-[0_10px_25px_rgba(251,191,36,0.35)] hover:shadow-[0_14px_30px_rgba(251,191,36,0.45)] hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 flex items-center justify-center">
            Absen Sekarang
        </a>
    </div>

    <script>
        const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]').content.replace(/\/$/, '');

        // ─── Token guard: redirect to login if no token ────────────────────
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = `${APP_BASE_URL}/login`;
        }

        // ─── Check face registration via API ───────────────────────────────
        // Since page routes are public (no server-side auth), we verify the
        // employee's face registration status client-side via the /api/me endpoint.
        (async function checkFaceRegistration() {
            try {
                const res = await fetch(`${APP_BASE_URL}/api/me`, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    const data = await res.json();
                    if (!data.face_registered) {
                        window.location.href = `${APP_BASE_URL}/face-registration`;
                        return;
                    }
                }
            } catch (e) {
                // Silently fail — page still renders with localStorage data
            }
        })();

        document.getElementById('employeeName').textContent = 'Selamat datang, ' + (localStorage.getItem('employee_name') || '');
        document.getElementById('displayName').textContent = localStorage.getItem('employee_name') || 'Karyawan';
        document.getElementById('displayPin').textContent = 'PIN: ' + (localStorage.getItem('employee_pin') || '-');

        document.getElementById('logoutBtn').addEventListener('click', async function() {
            try {
                await fetch(`${APP_BASE_URL}/api/logout`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    },
                });
            } catch (e) {}

            localStorage.removeItem('token');
            localStorage.removeItem('employee_name');
            localStorage.removeItem('employee_pin');
            window.location.href = `${APP_BASE_URL}/login`;
        });
    </script>

    @include('partials.bottom-nav')
</body>
</html>