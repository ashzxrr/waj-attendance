<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registrasi Wajah - WAJ Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        #video {
            width: 100%;
            max-width: 360px;
            height: auto;
            border-radius: 1rem;
            transform: scaleX(-1);
            background: #1e293b;
        }
        #overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .video-wrapper {
            position: relative;
            display: inline-block;
            max-width: 360px;
            width: 100%;
        }
        .thumbnail {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 0.75rem;
            transform: scaleX(-1);
        }
        .loading-spinner {
            border: 3px solid rgba(59, 130, 246, 0.2);
            border-top-color: #3b82f6;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 0.75rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-sm">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-lg shadow-emerald-500/30 mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Registrasi Wajah</h1>
            <p class="text-slate-400 text-sm mt-1">Daftarkan wajah Anda untuk absensi</p>
        </div>

        <!-- Main Card -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-5 shadow-xl border border-slate-700/50">

            <!-- Loading State -->
            <div id="loadingState" class="text-center py-8">
                <div class="loading-spinner"></div>
                <p class="text-slate-300 text-sm">Memuat sistem pengenalan wajah...</p>
                <p class="text-slate-500 text-xs mt-1">Mengunduh model deteksi</p>
            </div>

            <!-- Camera & Capture UI (hidden until models load) -->
            <div id="cameraUi" class="hidden space-y-4">
                <!-- Instruction -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-3 text-sm text-blue-300 text-center">
                    Posisikan wajah Anda di dalam bingkai, lalu tekan tombol untuk mengambil 3 foto dari sedikit posisi berbeda (depan, kiri sedikit, kanan sedikit)
                </div>

                <!-- Video + Overlay -->
                <div class="flex justify-center">
                    <div class="video-wrapper">
                        <video id="video" autoplay playsinline></video>
                        <!-- Oval face guide overlay -->
                        <svg id="overlay" viewBox="0 0 360 270" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <ellipse cx="180" cy="135" rx="90" ry="105" stroke="rgba(59,130,246,0.4)" stroke-width="2" stroke-dasharray="6 4"/>
                        </svg>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-xl p-3 text-center">
                </div>

                <!-- Capture Progress -->
                <div class="flex items-center justify-center gap-3">
                    <div class="flex gap-2">
                        <div id="dot1" class="w-3 h-3 rounded-full bg-slate-600 transition-colors"></div>
                        <div id="dot2" class="w-3 h-3 rounded-full bg-slate-600 transition-colors"></div>
                        <div id="dot3" class="w-3 h-3 rounded-full bg-slate-600 transition-colors"></div>
                    </div>
                    <span id="captureCounter" class="text-slate-400 text-sm">0 / 3</span>
                </div>

                <!-- Thumbnails -->
                <div id="thumbnails" class="flex justify-center gap-2 min-h-[72px]">
                </div>

                <!-- Capture Button -->
                <button
                    id="captureBtn"
                    class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-semibold text-lg rounded-xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Ambil Foto
                </button>

                <!-- Submit Button (hidden until 3 captures) -->
                <button
                    id="submitBtn"
                    class="hidden w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold text-lg rounded-xl shadow-lg shadow-blue-600/25 hover:shadow-blue-500/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Simpan Registrasi Wajah
                </button>
            </div>

            <!-- Camera Error (shown if getUserMedia fails) -->
            <div id="cameraError" class="hidden text-center py-8">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p class="text-red-400 text-sm mb-3">Kamera tidak dapat diakses.</p>
                <p class="text-slate-400 text-xs mb-4">Pastikan Anda mengizinkan akses kamera.</p>
                <button onclick="location.reload()" class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-xl transition-colors">
                    Coba Lagi
                </button>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>

    <script>
        // ─── State ──────────────────────────────────────────────────────────
        const captures = []; // Array of { descriptor: number[], photo: string (base64) }
        const MAX_CAPTURES = 3;
        let videoStream = null;
        let isProcessing = false;

        // ─── DOM refs ───────────────────────────────────────────────────────
        const video = document.getElementById('video');
        const loadingState = document.getElementById('loadingState');
        const cameraUi = document.getElementById('cameraUi');
        const cameraError = document.getElementById('cameraError');
        const errorMsg = document.getElementById('errorMessage');
        const captureBtn = document.getElementById('captureBtn');
        const submitBtn = document.getElementById('submitBtn');
        const captureCounter = document.getElementById('captureCounter');
        const thumbnails = document.getElementById('thumbnails');
        const dots = [document.getElementById('dot1'), document.getElementById('dot2'), document.getElementById('dot3')];

        // ─── Helper: show error inline ──────────────────────────────────────
        function showError(msg) {
            errorMsg.textContent = msg;
            errorMsg.classList.remove('hidden');
        }

        function hideError() {
            errorMsg.classList.add('hidden');
        }

        // ─── Helper: update progress UI ─────────────────────────────────────
        function updateProgress() {
            const count = captures.length;
            captureCounter.textContent = count + ' / ' + MAX_CAPTURES;
            for (let i = 0; i < MAX_CAPTURES; i++) {
                dots[i].classList.toggle('bg-emerald-500', i < count);
                dots[i].classList.toggle('bg-slate-600', i >= count);
            }

            // Show submit button when all 3 captured
            if (count >= MAX_CAPTURES) {
                captureBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                captureBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        // ─── Helper: add thumbnail ──────────────────────────────────────────
        function addThumbnail(base64Photo) {
            const img = document.createElement('img');
            img.src = base64Photo;
            img.className = 'thumbnail border-2 border-emerald-500/50';
            img.alt = 'Foto ' + captures.length;
            thumbnails.appendChild(img);
        }

        // ─── 1. Load face-api.js models ────────────────────────────────────
        async function loadModels() {
            const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
        }

        // ─── 2. Start camera ────────────────────────────────────────────────
        async function startCamera() {
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false,
                });
                video.srcObject = videoStream;
                await video.play();
            } catch (err) {
                loadingState.classList.add('hidden');
                cameraError.classList.remove('hidden');
                throw err;
            }
        }

        // ─── 3. Capture face ────────────────────────────────────────────────
        async function captureFace() {
            if (isProcessing) return;
            isProcessing = true;
            captureBtn.disabled = true;
            captureBtn.textContent = 'Memproses...';
            hideError();

            try {
                // Run face detection + landmarks + descriptor on current video frame
                const result = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!result) {
                    showError('Wajah tidak terdeteksi, coba lagi');
                    isProcessing = false;
                    captureBtn.disabled = false;
                    captureBtn.textContent = 'Ambil Foto';
                    return;
                }

                // Convert Float32Array descriptor to plain array for JSON
                const descriptor = Array.from(result.descriptor);

                // Capture frame as base64 JPEG via canvas
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                // Flip horizontally to match the mirrored video display
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0);
                const base64Photo = canvas.toDataURL('image/jpeg', 0.85);

                // Store capture
                captures.push({ descriptor, photo: base64Photo });
                addThumbnail(base64Photo);
                updateProgress();

                if (captures.length < MAX_CAPTURES) {
                    captureBtn.textContent = 'Ambil Foto (' + (MAX_CAPTURES - captures.length) + ' lagi)';
                } else {
                    captureBtn.textContent = 'Selesai';
                }
            } catch (err) {
                showError('Terjadi kesalahan saat memproses wajah');
                console.error(err);
            }

            isProcessing = false;
            captureBtn.disabled = false;
        }

        // ─── 4. Average descriptors & submit ────────────────────────────────
        async function submitRegistration() {
            if (captures.length < MAX_CAPTURES) return;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            hideError();

            try {
                // ────────────────────────────────────────────────────────────
                // Average the 3 descriptors element-wise to produce a single
                // robust face embedding. Each descriptor is a Float32Array of
                // 128 floats. We compute the arithmetic mean per dimension.
                // ────────────────────────────────────────────────────────────
                const dims = captures[0].descriptor.length; // 128
                const finalDescriptor = new Array(dims).fill(0);

                for (const cap of captures) {
                    for (let i = 0; i < dims; i++) {
                        finalDescriptor[i] += cap.descriptor[i] / MAX_CAPTURES;
                    }
                }

                // Use the last captured photo as the reference
                const finalPhoto = captures[captures.length - 1].photo;

                const token = localStorage.getItem('token');
                const response = await fetch('/face-registration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        descriptor: JSON.stringify(finalDescriptor),
                        photo: finalPhoto,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Registrasi gagal');
                }

                // Success
                document.getElementById('cameraUi').innerHTML = `
                    <div class="text-center py-6">
                        <svg class="w-16 h-16 text-emerald-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-emerald-400 text-lg font-semibold">Registrasi wajah berhasil!</p>
                        <p class="text-slate-400 text-sm mt-1">Mengalihkan ke dashboard...</p>
                    </div>
                `;

                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1500);
            } catch (err) {
                showError(err.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Registrasi Wajah';
                // Reset captures so user can retry
                captures.length = 0;
                thumbnails.innerHTML = '';
                updateProgress();
                captureBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        // ─── 5. Cleanup on page unload ──────────────────────────────────────
        window.addEventListener('beforeunload', function () {
            if (videoStream) {
                videoStream.getTracks().forEach(t => t.stop());
            }
        });

        // ─── 6. Init ────────────────────────────────────────────────────────
        (async function init() {
            try {
                await loadModels();
                await startCamera();
                loadingState.classList.add('hidden');
                cameraUi.classList.remove('hidden');
                updateProgress();
            } catch (err) {
                // Error already handled in startCamera for camera issues
                if (!cameraError.classList.contains('hidden')) return;
                loadingState.innerHTML = `
                    <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-400 text-sm">Gagal memuat sistem pengenalan wajah.</p>
                    <p class="text-slate-400 text-xs mt-1">Periksa koneksi internet Anda.</p>
                `;
            }
        })();

        // ─── Event listeners ────────────────────────────────────────────────
        captureBtn.addEventListener('click', captureFace);
        submitBtn.addEventListener('click', submitRegistration);
    </script>
</body>
</html>