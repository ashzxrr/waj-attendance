<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - WAJ Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 shadow-lg shadow-blue-500/30 mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">WAJ Attendance</h1>
            <p class="text-slate-400 mt-1 text-sm">Masuk menggunakan PIN karyawan</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-6 shadow-xl border border-slate-700/50">
            <form id="loginForm" class="space-y-5">
                @csrf

                <!-- Error Message -->
                <div id="errorMessage" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-xl p-3 text-center">
                </div>

                <!-- PIN Karyawan -->
                <div>
                    <label for="pin" class="block text-sm font-medium text-slate-300 mb-1.5">PIN Karyawan</label>
                    <input
                        type="number"
                        id="pin"
                        name="pin"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="Masukkan PIN"
                        required
                        autocomplete="off"
                        class="w-full px-4 py-3.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                </div>

                <!-- Kode Absensi -->
                <div>
                    <label for="pin_absensi" class="block text-sm font-medium text-slate-300 mb-1.5">Kode Absensi</label>
                    <input
                        type="password"
                        id="pin_absensi"
                        name="pin_absensi"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        placeholder="******"
                        required
                        autocomplete="off"
                        class="w-full px-4 py-3.5 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    >
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold text-lg rounded-xl shadow-lg shadow-blue-600/25 hover:shadow-blue-500/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Masuk
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>

    <script>
        // Simple device fingerprint generator
        function generateDeviceId() {
            let stored = localStorage.getItem('device_id');
            if (stored) return stored;

            const raw = navigator.userAgent + '|' + screen.width + 'x' + screen.height + '|' + navigator.language;
            let hash = 0;
            for (let i = 0; i < raw.length; i++) {
                const chr = raw.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0;
            }
            const deviceId = 'dev_' + Math.abs(hash).toString(16) + '_' + Date.now().toString(36);
            localStorage.setItem('device_id', deviceId);
            return deviceId;
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const errorDiv = document.getElementById('errorMessage');
            const pin = document.getElementById('pin').value.trim();
            const pinAbsensi = document.getElementById('pin_absensi').value.trim();

            // Reset error
            errorDiv.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        pin: pin,
                        pin_absensi: pinAbsensi,
                        device_id: generateDeviceId(),
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'PIN atau kode absensi salah');
                }

                // Save token
                localStorage.setItem('token', data.token);
                localStorage.setItem('employee_name', data.employee.nama);
                localStorage.setItem('employee_pin', data.employee.pin);

                // Redirect based on face registration status
                if (data.face_registered) {
                    window.location.href = '/dashboard';
                } else {
                    window.location.href = '/face-registration';
                }
            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Masuk';
            }
        });

        // Auto-focus PIN field on load
        document.getElementById('pin').focus();
    </script>
</body>
</html>