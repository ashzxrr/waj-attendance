<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    @include('partials.base-url-meta')
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="flex items-center justify-start p-4 min-h-screen">
    <div class="w-full max-w-lg mx-auto pb-28">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-white">Profile</h1>
                <p class="text-slate-400 text-sm">Kelola informasi dan kode absensi Anda</p>
            </div>
            <button id="logoutBtn" class="px-4 py-2 bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 text-sm rounded-xl transition-colors">
                Logout
            </button>
        </div>

        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-slate-700/50 space-y-6">
            <div class="flex items-center gap-4">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-slate-400 text-sm">Nama</p>
                    <p id="displayName" class="text-white font-semibold text-lg">-</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 text-sm text-slate-300">
                <div class="rounded-2xl bg-slate-900/80 p-4 border border-slate-700/60">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.2em] mb-1">NIK</p>
                    <p id="displayNik" class="text-white font-medium">-</p>
                </div>
                <div class="rounded-2xl bg-slate-900/80 p-4 border border-slate-700/60">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.2em] mb-1">PIN</p>
                    <p id="displayPin" class="text-white font-medium">-</p>
                </div>
                <div class="rounded-2xl bg-slate-900/80 p-4 border border-slate-700/60">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.2em] mb-1">Status wajah</p>
                    <p id="displayFaceStatus" class="text-emerald-300 font-semibold">Memuat...</p>
                </div>
                <div class="rounded-2xl bg-slate-900/80 p-4 border border-slate-700/60">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.2em] mb-1">Login terakhir</p>
                    <p id="displayLastLogin" class="text-white font-medium">-</p>
                </div>
                <div class="rounded-2xl bg-slate-900/80 p-4 border border-slate-700/60">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.2em] mb-1">Device ID</p>
                    <p id="displayDeviceId" class="text-white font-medium break-all">-</p>
                </div>
            </div>

            <div class="rounded-3xl bg-slate-900/80 p-5 border border-slate-700/60">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-white font-semibold">Ubah Kode Absensi</p>
                        <p class="text-slate-500 text-sm">Gunakan kode 6 digit baru yang mudah diingat.</p>
                    </div>
                </div>

                <div id="messageBox" class="hidden rounded-2xl p-4 text-sm"></div>

                <form id="changePinForm" class="space-y-4">
                    <div>
                        <label for="current_pin_absensi" class="block text-sm font-medium text-slate-300 mb-1">Kode Absensi Lama</label>
                        <input type="password" id="current_pin_absensi" name="current_pin_absensi" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="******" required autocomplete="off" class="w-full px-4 py-3.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="new_pin_absensi" class="block text-sm font-medium text-slate-300 mb-1">Kode Absensi Baru</label>
                        <input type="password" id="new_pin_absensi" name="new_pin_absensi" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="******" required autocomplete="off" class="w-full px-4 py-3.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="new_pin_absensi_confirmation" class="block text-sm font-medium text-slate-300 mb-1">Konfirmasi Kode Absensi Baru</label>
                        <input type="password" id="new_pin_absensi_confirmation" name="new_pin_absensi_confirmation" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="******" required autocomplete="off" class="w-full px-4 py-3.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>

                    <button type="submit" id="submitBtn" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-semibold text-lg rounded-xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('partials.bottom-nav')

    <script>
        const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]').content.replace(/\/$/, '');
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = `${APP_BASE_URL}/login`;
        }

        const displayName = document.getElementById('displayName');
        const displayNik = document.getElementById('displayNik');
        const displayPin = document.getElementById('displayPin');
        const displayFaceStatus = document.getElementById('displayFaceStatus');
        const displayLastLogin = document.getElementById('displayLastLogin');
        const displayDeviceId = document.getElementById('displayDeviceId');
        const messageBox = document.getElementById('messageBox');
        const changePinForm = document.getElementById('changePinForm');
        const logoutBtn = document.getElementById('logoutBtn');

        function showMessage(type, text) {
            messageBox.textContent = text;
            messageBox.classList.remove('hidden', 'bg-emerald-500/10', 'text-emerald-300', 'border', 'border-emerald-500/30', 'bg-red-500/10', 'text-red-300', 'border-red-500/30');
            if (type === 'success') {
                messageBox.classList.add('bg-emerald-500/10', 'text-emerald-300', 'border', 'border-emerald-500/30');
            } else {
                messageBox.classList.add('bg-red-500/10', 'text-red-300', 'border', 'border-red-500/30');
            }
        }

        function hideMessage() {
            messageBox.classList.add('hidden');
        }

        async function loadProfile() {
            try {
                const response = await fetch(`${APP_BASE_URL}/api/profile`, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat data profil.');
                }

                const data = await response.json();
                displayName.textContent = data.nama || '-';
                displayNik.textContent = data.nik || '-';
                displayPin.textContent = data.pin || '-';
                displayFaceStatus.textContent = data.face_registered ? 'Teregistrasi' : 'Belum teregistrasi';
                displayFaceStatus.className = data.face_registered ? 'text-emerald-300 font-semibold' : 'text-amber-300 font-semibold';
                displayLastLogin.textContent = data.last_login_at ? new Date(data.last_login_at).toLocaleString('id-ID') : '-';
                displayDeviceId.textContent = data.device_id || '-';
            } catch (err) {
                showMessage('error', err.message);
            }
        }

        changePinForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            hideMessage();

            const currentPin = document.getElementById('current_pin_absensi').value.trim();
            const newPin = document.getElementById('new_pin_absensi').value.trim();
            const confirmPin = document.getElementById('new_pin_absensi_confirmation').value.trim();

            if (currentPin.length !== 6 || newPin.length !== 6 || confirmPin.length !== 6) {
                showMessage('error', 'Semua kode harus 6 digit.');
                return;
            }

            if (newPin !== confirmPin) {
                showMessage('error', 'Konfirmasi kode absensi tidak cocok.');
                return;
            }

            try {
                const response = await fetch(`${APP_BASE_URL}/api/profile/change-pin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                    },
                    body: JSON.stringify({
                        current_pin_absensi: currentPin,
                        new_pin_absensi: newPin,
                        new_pin_absensi_confirmation: confirmPin,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengubah kode absensi.');
                }

                showMessage('success', data.message || 'PIN absensi berhasil diubah.');
                changePinForm.reset();
            } catch (err) {
                showMessage('error', err.message);
            }
        });

        logoutBtn.addEventListener('click', async function () {
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

        loadProfile();
    </script>
</body>
</html>
