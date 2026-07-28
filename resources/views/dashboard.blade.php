<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WAJ Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="p-4">
    <div class="max-w-lg mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-white">Dashboard</h1>
                <p class="text-slate-400 text-sm" id="employeeName">Selamat datang</p>
            </div>
            <button id="logoutBtn" class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm rounded-xl transition-colors">
                Logout
            </button>
        </div>

        <!-- Info Card -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-slate-700/50 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-lg shadow-emerald-500/30 mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-white mb-1" id="displayName">Karyawan</h2>
            <p class="text-slate-400 text-sm" id="displayPin">PIN: -</p>
            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-500/10 rounded-xl">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-emerald-400 text-sm">Wajah terdaftar</span>
            </div>
        </div>

        <!-- Absen Button -->
        <a href="/absen" class="block mt-4 w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold text-lg text-center rounded-xl shadow-lg shadow-blue-600/25 hover:shadow-blue-500/40 transition-all duration-200 active:scale-[0.98]">
            Absen Sekarang
        </a>
    </div>

    <script>
        // ─── Token guard: redirect to login if no token ────────────────────
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
        }

        // ─── Check face registration via API ───────────────────────────────
        // Since page routes are public (no server-side auth), we verify the
        // employee's face registration status client-side via the /api/me endpoint.
        (async function checkFaceRegistration() {
            try {
                const res = await fetch('/api/me', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    const data = await res.json();
                    if (!data.face_registered) {
                        window.location.href = '/face-registration';
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
                await fetch('/api/logout', {
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
            window.location.href = '/login';
        });
    </script>
</body>
</html>