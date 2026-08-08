<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - WAJ Attendance</title>
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
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* ===== Error message icon (pseudo-element so showError() textContent stays intact) ===== */
        #errorMessage::before {
            content: '';
            display: inline-block;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23ef4444' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }

        /* ===== Animations ===== */
        @keyframes fadeSlideDown {
            from { opacity: 0; transform: translateY(-18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes floatSlow {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(14px, -22px) scale(1.05); }
        }
        @keyframes floatSlower {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-16px, 18px) scale(1.08); }
        }
        @keyframes pulseSoft {
            0%, 100% { opacity: 0.45; }
            50% { opacity: 0.85; }
        }

        .anim-fade-slide-down { animation: fadeSlideDown 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .anim-fade-slide-up { animation: fadeSlideUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .anim-float-slow { animation: floatSlow 10s ease-in-out infinite; }
        .anim-float-slower { animation: floatSlower 14s ease-in-out infinite; }
        .anim-pulse-soft { animation: pulseSoft 6s ease-in-out infinite; }
    </style>
</head>
<body class="relative flex items-center justify-center min-h-screen px-4 py-8 overflow-x-hidden">

    <!-- ===== Decorative Background (soft gradient blobs) ===== -->
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 -right-24 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-amber-200/40 blur-3xl anim-float-slow"></div>
        <div class="absolute top-10 right-10 w-24 h-24 rounded-full bg-amber-300/30 blur-2xl anim-float-slower"></div>
        <div class="absolute -bottom-28 -left-24 w-80 h-80 sm:w-[28rem] sm:h-[28rem] rounded-full bg-yellow-200/60 blur-3xl anim-float-slower"></div>
        <div class="absolute bottom-16 left-10 w-28 h-28 rounded-full bg-amber-200/40 blur-2xl anim-float-slow"></div>
        <div class="absolute top-1/3 left-6 w-2 h-2 rounded-full bg-amber-300/50"></div>
        <div class="absolute top-1/4 right-1/4 w-1.5 h-1.5 rounded-full bg-amber-400/40"></div>
        <div class="absolute bottom-1/3 right-12 w-2 h-2 rounded-full bg-amber-300/50"></div>
        <div class="absolute top-2/3 left-1/3 w-1.5 h-1.5 rounded-full bg-amber-400/40"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[36rem] h-[36rem] rounded-full bg-amber-100/30 blur-3xl anim-pulse-soft"></div>
    </div>

    <!-- ===== Main Content ===== -->
    <div class="relative z-10 w-full max-w-[440px]">
        <!-- Logo / Header -->
        <div class="text-center mb-7 anim-fade-slide-down">
            <div class="inline-flex items-center justify-center w-[100px] h-[100px] sm:w-[110px] sm:h-[110px] mb-4 rounded-[28px] overflow-hidden bg-white/80 p-2 shadow-[0_12px_28px_rgba(251,191,36,0.25)]">
                <img src="{{ asset('images/logo.png') }}" alt="WAJ Attendance"
                     class="w-full h-full object-contain rounded-[20px]" />
            </div>
            <h1 class="text-[28px] font-extrabold tracking-tight text-[#111827] leading-none">Attendance</h1>
            <h2 class="mt-6 text-2xl sm:text-[26px] font-bold text-[#111827] leading-tight">Selamat datang! 👋</h2>
            <p class="mt-2 text-sm sm:text-[15px] text-gray-500">Masuk untuk melanjutkan absensi Anda</p>
        </div>

        <!-- Biometric Card -->
        <div id="biometricCard" class="hidden anim-fade-slide-up mb-8 bg-white/80 backdrop-blur-xl rounded-[28px] border border-amber-200/40 shadow-[0_20px_60px_rgba(180,140,30,0.12)] p-7">
            <div class="flex flex-col items-center gap-5 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-amber-100 to-yellow-50 border border-amber-200/60">
                    <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-3.866-3.134-7-7-7m14 0c-3.866 0-7 3.134-7 7m0 0v4m0 4a4 4 0 11-4-4m4 4a8 8 0 108-8m-8 0v4" />
                    </svg>
                </div>
                <div>
                    <p class="text-[#111827] text-lg font-bold">Masuk dengan Sidik Jari</p>
                    <p id="biometricStatus" class="text-gray-500 text-sm mt-2">Memeriksa sidik jari...</p>
                </div>
                <button id="biometricBtn" type="button" class="w-full h-[54px] bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#111827] font-bold text-base rounded-2xl shadow-[0_10px_25px_rgba(251,191,36,0.35)] hover:shadow-[0_14px_30px_rgba(251,191,36,0.45)] hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200">
                    Login dengan Sidik Jari
                </button>
                <button id="usePinBtn" type="button" class="w-full h-[54px] bg-white text-gray-700 font-semibold text-base rounded-2xl border border-amber-200 hover:bg-amber-50 hover:border-amber-300 active:scale-[0.98] transition-all duration-200">
                    Login dengan PIN
                </button>
            </div>
        </div>

        <!-- Login Card -->
        <div id="loginCard" class="anim-fade-slide-up bg-white/90 backdrop-blur-xl rounded-[28px] border border-amber-200/40 shadow-[0_20px_60px_rgba(180,140,30,0.12)] p-6 sm:p-8">
            <form id="loginForm" class="space-y-5">
                @csrf

                <!-- Error Message -->
                <div id="errorMessage" class="hidden flex items-center justify-center gap-2.5 bg-red-50 border border-red-200 text-red-600 text-sm font-medium rounded-2xl px-4 py-3">
                </div>

                <!-- PIN Karyawan -->
                <div>
                    <label for="pin" class="block text-sm font-semibold text-gray-700 mb-2">PIN Karyawan</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        <input
                            type="number"
                            id="pin"
                            name="pin"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            placeholder="Masukkan PIN"
                            required
                            autocomplete="off"
                            class="w-full h-[54px] pl-12 pr-4 bg-white border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 text-base font-medium focus:outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200"
                        >
                    </div>
                </div>

                <!-- Kode Absensi -->
                <div>
                    <label for="pin_absensi" class="block text-sm font-semibold text-gray-700 mb-2">Kode Absensi</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="pin_absensi"
                            name="pin_absensi"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            placeholder="Kode Absensi 6 digit"
                            required
                            autocomplete="off"
                            class="w-full h-[54px] pl-12 pr-12 bg-white border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 text-base font-medium tracking-widest focus:outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200"
                        >
                        <button type="button" id="togglePassword" aria-label="Tampilkan kode absensi"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-amber-500 transition-colors">
                            <svg id="eyeOpenIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeClosedIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full h-[54px] bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#111827] font-bold text-base rounded-2xl shadow-[0_10px_25px_rgba(251,191,36,0.35)] hover:shadow-[0_14px_30px_rgba(251,191,36,0.45)] hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                >
                    Masuk
                </button>

                <!-- Divider -->
                <div class="flex items-center gap-3 pt-1">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs font-medium text-gray-400">atau</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <!-- Biometric Login Button -->
                <button type="button" id="biometricLoginButton"
                    class="w-full h-[54px] bg-white text-gray-800 font-semibold text-base rounded-2xl border-2 border-amber-200 hover:bg-[#FFF7D6] hover:border-amber-300 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2.5">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-3.866-3.134-7-7-7m14 0c-3.866 0-7 3.134-7 7m0 0v4m0 4a4 4 0 11-4-4m4 4a8 8 0 108-8m-8 0v4" />
                    </svg>
                    Masuk dengan Sidik Jari
                </button>
            </form>
        </div>

        <!-- Camera & Location Info -->
        <div class="flex items-center justify-center gap-2 text-xs text-gray-500 mt-5 anim-fade-slide-up">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.18 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Pastikan kamera &amp; lokasi diizinkan untuk verifikasi wajah</span>
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>

        <!-- Support / Help -->
        <div class="mt-6 text-center anim-fade-slide-up">
            <div class="bg-white/70 backdrop-blur-md rounded-2xl border border-amber-200/40 px-5 py-4 shadow-[0_10px_30px_rgba(180,140,30,0.08)]">
                <p class="text-sm font-semibold text-gray-700">Butuh bantuan?</p>
                <p class="text-xs text-gray-500 mt-1">Belum punya aplikasi atau mengalami kendala login?</p>
                <a href="https://wa.me/6285184625082?text=Halo%20Admin%20IT%2C%20saya%20butuh%20bantuan%20untuk%20login%20ke%20sistem%20absensi." target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 mt-3 text-sm font-bold text-amber-600 hover:text-amber-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-8.08-7.071c3.904-3.905 10.236-3.905 14.14 0M6.93 9.5c4.142-4.142 10.858-4.142 15 0" />
                    </svg>
                    Hubungi Admin IT
                </a>
                <p class="text-base font-extrabold text-[#111827] mt-1.5 tracking-wide">+62 851-8462-5082</p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center mt-8 anim-fade-slide-up">
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} WAJ Attendance System</p>
            <p class="text-[11px] text-gray-400 mt-1">Absensi mudah, akurat &amp; aman</p>
        </footer>
    </div>

    <script>
        // Biometric authentication here is device-local convenience only — actual authorization still happens fully server-side via the existing /api/login endpoint (PIN + kode absensi verification unchanged); the biometric layer only automates retrieving and submitting those same credentials from the device's secure keystore after the phone's fingerprint sensor confirms the device owner's identity.
        const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]').content.replace(/\/$/, '');
        const nativeBiometric = window?.Capacitor?.Plugins?.NativeBiometric ?? null;
        let biometricEnrolled = localStorage.getItem('biometric_enrolled') === 'true';

        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorDiv = document.getElementById('errorMessage');
        const pinField = document.getElementById('pin');
        const pinAbsensiField = document.getElementById('pin_absensi');
        const loginCard = document.getElementById('loginCard');
        const biometricCard = document.getElementById('biometricCard');
        const biometricStatus = document.getElementById('biometricStatus');
        const biometricBtn = document.getElementById('biometricBtn');
        const usePinBtn = document.getElementById('usePinBtn');

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

        function hideError() {
            errorDiv.classList.add('hidden');
            errorDiv.textContent = '';
        }

        function showError(message) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function showManualLogin() {
            biometricCard.classList.add('hidden');
            loginCard.classList.remove('hidden');
            pinField.focus();
        }

        function showBiometricLogin() {
            loginCard.classList.add('hidden');
            biometricCard.classList.remove('hidden');
        }

        async function isBiometricAvailable() {
            if (!nativeBiometric) {
                return false;
            }

            try {
                await nativeBiometric.isAvailable();
                return true;
            } catch (err) {
                return false;
            }
        }

        async function promptBiometricEnrollment(pin, pinAbsensi) {
            if (!nativeBiometric) {
                return;
            }

            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-50 bg-[#FFFDF7]/80 backdrop-blur-md flex items-center justify-center p-4';
            overlay.innerHTML = `
                <div class="w-full max-w-sm rounded-3xl bg-white border border-amber-200/50 p-6 shadow-[0_20px_60px_rgba(180,140,30,0.18)]">
                    <h2 class="text-[#111827] text-lg font-bold mb-3">Aktifkan login sidik jari untuk selanjutnya?</h2>
                    <p class="text-gray-500 text-sm mb-6">Jika Anda setuju, PIN dan kode absensi Anda akan disimpan secara aman di perangkat dan hanya bisa digunakan oleh sidik jari yang berhasil diverifikasi.</p>
                    <div class="flex gap-3">
                        <button id="confirmBiometricYes" type="button" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#111827] font-bold transition-all duration-200">Ya, aktifkan</button>
                        <button id="confirmBiometricNo" type="button" class="flex-1 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-colors">Tidak</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            return new Promise((resolve) => {
                const yesBtn = document.getElementById('confirmBiometricYes');
                const noBtn = document.getElementById('confirmBiometricNo');

                const cleanup = () => {
                    overlay.remove();
                };

                yesBtn.addEventListener('click', async () => {
                    cleanup();
                    try {
                        await nativeBiometric.setCredentials({
                            username: pin,
                            password: pinAbsensi,
                            server: 'waj-attendance',
                        });
                        localStorage.setItem('biometric_enrolled', 'true');
                        biometricEnrolled = true;
                    } catch (err) {
                        console.warn('Biometric enrollment failed:', err);
                    }
                    resolve();
                }, { once: true });

                noBtn.addEventListener('click', () => {
                    cleanup();
                    resolve();
                }, { once: true });
            });
        }

        async function redirectAfterLogin(data) {
            if (data.face_registered) {
                window.location.href = `${APP_BASE_URL}/dashboard`;
            } else {
                window.location.href = `${APP_BASE_URL}/face-registration`;
            }
        }

        async function submitLogin(pin, kodeAbsensi) {
            hideError();
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';

            try {
                const response = await fetch(`${APP_BASE_URL}/api/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        pin: pin,
                        pin_absensi: kodeAbsensi,
                        device_id: generateDeviceId(),
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'PIN atau kode absensi salah');
                }

                localStorage.setItem('token', data.token);
                localStorage.setItem('employee_name', data.employee.nama);
                localStorage.setItem('employee_pin', data.employee.pin);

                if (!biometricEnrolled && await isBiometricAvailable()) {
                    await promptBiometricEnrollment(pin, kodeAbsensi);
                }

                await redirectAfterLogin(data);
            } catch (err) {
                showError(err.message || 'Terjadi kesalahan saat login.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Masuk';
            }
        }

        async function runBiometricLogin() {
            if (!await isBiometricAvailable()) {
                showError('Biometrik tidak tersedia di perangkat ini. Silakan gunakan PIN.');
                showManualLogin();
                return;
            }

            biometricStatus.textContent = 'Meminta verifikasi sidik jari...';

            try {
                if (typeof nativeBiometric.verifyIdentity === 'function') {
                    await nativeBiometric.verifyIdentity({ reason: 'Verifikasi sidik jari untuk masuk' });
                }

                const creds = await nativeBiometric.getCredentials({
                    server: 'waj-attendance',
                });

                if (!creds || !creds.username || !creds.password) {
                    throw new Error('Kredensial biometrik tidak ditemukan.');
                }

                await submitLogin(creds.username, creds.password);
            } catch (err) {
                biometricStatus.textContent = 'Verifikasi gagal atau dibatalkan. Silakan gunakan PIN.';
                showManualLogin();
            }
        }

        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const pin = pinField.value.trim();
            const pinAbsensi = pinAbsensiField.value.trim();
            await submitLogin(pin, pinAbsensi);
        });

        biometricBtn.addEventListener('click', async function() {
            await runBiometricLogin();
        });

        usePinBtn.addEventListener('click', function() {
            showManualLogin();
        });

        document.addEventListener('DOMContentLoaded', async function() {
            if (biometricEnrolled && nativeBiometric) {
                showBiometricLogin();
                await runBiometricLogin();
            } else {
                showManualLogin();
            }
        });

        pinField.focus();

        // ===== UI-only additions (do not affect authentication logic) =====

        // Show/hide password toggle
        const togglePassword = document.getElementById('togglePassword');
        const eyeOpenIcon = document.getElementById('eyeOpenIcon');
        const eyeClosedIcon = document.getElementById('eyeClosedIcon');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const isPassword = pinAbsensiField.type === 'password';
                pinAbsensiField.type = isPassword ? 'text' : 'password';
                eyeOpenIcon.classList.toggle('hidden');
                eyeClosedIcon.classList.toggle('hidden');
            });
        }

        // Fingerprint button inside the login card
        const biometricLoginButton = document.getElementById('biometricLoginButton');
        if (biometricLoginButton) {
            biometricLoginButton.addEventListener('click', async function() {
                await runBiometricLogin();
            });
        }
    </script>
</body>
</html>