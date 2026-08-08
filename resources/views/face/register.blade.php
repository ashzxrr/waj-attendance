<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registrasi Wajah - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    @include('partials.base-url-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
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
        #video {
            width: 100%;
            max-width: 360px;
            height: auto;
            border-radius: 1.25rem;
            transform: scaleX(-1);
            background: #fff7d6;
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
            border: 3px solid rgba(16, 185, 129, 0.2);
            border-top-color: #34d399;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 0.75rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        /* Pulsing ring shown around the camera while detection is running */
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
            70% { box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .processing-active {
            animation: pulse-ring 1.2s ease-out infinite;
        }
    </style>
</head>
<body class="flex items-center justify-center px-4 py-5 min-h-screen">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-300 to-amber-500 shadow-[0_10px_25px_rgba(251,191,36,0.25)] mb-3">
                <svg class="w-8 h-8 text-[#111827]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-[#111827]">Registrasi Wajah</h1>
            <p class="text-gray-500 text-sm mt-1">Daftarkan wajah Anda untuk absensi</p>
        </div>

        <div class="soft-card rounded-[28px] p-5 sm:p-6">

            <!-- Loading State -->
            <div id="loadingState" class="text-center py-8">
                <div class="loading-spinner"></div>
                <p class="text-slate-300 text-sm">Memuat sistem pengenalan wajah...</p>
                <p class="text-slate-500 text-xs mt-1">Mengunduh model deteksi</p>
            </div>

            <!-- Camera & Capture UI (hidden until models load) -->
            <div id="cameraUi" class="hidden space-y-4">
                <!-- Instruction -->
                <div class="bg-[#FFF7D6] border border-amber-200 rounded-2xl p-3 text-sm text-amber-700 text-center">
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
                        <!-- Processing overlay: shown while detection is running -->
                        <div id="processingOverlay" class="hidden absolute inset-0 flex flex-col items-center justify-center rounded-[20px] bg-white/70 backdrop-blur-sm z-10">
                            <div class="w-10 h-10 border-4 border-amber-200 border-t-amber-500 rounded-full animate-spin mb-2"></div>
                            <p id="processingText" class="text-amber-700 text-sm font-medium">Mendeteksi wajah...</p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-600 text-sm rounded-2xl p-3 text-center">
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
                    class="w-full h-[54px] bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#111827] font-bold text-base rounded-2xl shadow-[0_10px_25px_rgba(251,191,36,0.35)] hover:shadow-[0_14px_30px_rgba(251,191,36,0.45)] hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    Ambil Foto
                </button>

                <!-- Submit Button (hidden until 3 captures) -->
                <button
                    id="submitBtn"
                    class="hidden w-full h-[54px] bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#111827] font-bold text-base rounded-2xl shadow-[0_10px_25px_rgba(251,191,36,0.35)] hover:shadow-[0_14px_30px_rgba(251,191,36,0.45)] hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
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
                <button onclick="location.reload()" class="px-5 py-2 bg-white border border-amber-200 hover:bg-[#FFF7D6] text-gray-700 text-sm rounded-2xl transition-colors">
                    Coba Lagi
                </button>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>

    <script>
        // ─── Token guard: redirect to login if no token ────────────────────
        // Page routes are publicly routable (no server-side auth middleware).
        // Auth is enforced client-side — if no Bearer token in localStorage,
        // redirect immediately before any other logic (models, camera, etc.)
        const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]').content.replace(/\/$/, '');
        const _token = localStorage.getItem('token');
        if (!_token) {
            window.location.href = `${APP_BASE_URL}/login`;
        }

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

        // ─── Processing feedback helpers ────────────────────────────────────
        const videoWrapper = document.querySelector('.video-wrapper');
        const processingOverlay = document.getElementById('processingOverlay');
        const processingText = document.getElementById('processingText');
        let slowTimer = null;

        function showProcessing() {
            processingOverlay.classList.remove('hidden');
            videoWrapper.classList.add('processing-active');
            processingText.textContent = 'Mendeteksi wajah...';
        }

        function hideProcessing() {
            processingOverlay.classList.add('hidden');
            videoWrapper.classList.remove('processing-active');
            clearTimeout(slowTimer);
        }

        // ─── Downscaled detection input ────────────────────────────────────
        // Face detection runs on a downscaled frame (max 480px wide) to cut
        // processing time on mobile. Detection accuracy is not meaningfully
        // affected for a face reasonably framed in view. The ORIGINAL
        // full-resolution video frame is still used separately for the actual
        // photo capture/upload, so photo quality stays high.
        const DETECT_MAX_WIDTH = 480;
        let detectCanvas = null;
        let detectCtx = null;

        function getDetectionCanvas() {
            if (!detectCanvas) {
                detectCanvas = document.createElement('canvas');
                detectCtx = detectCanvas.getContext('2d');
            }
            const scale = Math.min(1, DETECT_MAX_WIDTH / video.videoWidth);
            detectCanvas.width = Math.round(video.videoWidth * scale);
            detectCanvas.height = Math.round(video.videoHeight * scale);
            // Mirror horizontally to match the displayed (mirrored) video
            detectCtx.setTransform(-1, 0, 0, 1, detectCanvas.width, 0);
            detectCtx.drawImage(video, 0, 0, detectCanvas.width, detectCanvas.height);
            return detectCanvas;
        }

        // ─── Warm-up detection ─────────────────────────────────────────────
        // Runs one throwaway detection after models load to warm up the
        // model/WASM backend, so the first real user-triggered detection
        // isn't paying a one-time initialization cost on top of normal time.
        async function warmUpDetection() {
            try {
                await faceapi.detectSingleFace(
                    getDetectionCanvas(),
                    new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 })
                );
            } catch (e) {
                // Best-effort warm-up — ignore failures
            }
        }

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
        // ssdMobilenetv1 is used instead of tinyFaceDetector because it is
        // significantly more accurate at locating faces. This reduces both
        // missed detections and false matches. It is heavier to load, so the
        // loading indicator above stays visible while the weights download.
        async function loadModels() {
            if (window.faceApiModelsLoaded) {
                return;
            }

            if (!window.faceApiModelsLoadPromise) {
                const MODEL_URL = '{{ asset('vendor/face-api/models') }}';

                window.faceApiModelsLoadPromise = Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
                ])
                    .then(() => {
                        window.faceApiModelsLoaded = true;
                    })
                    .catch((err) => {
                        window.faceApiModelsLoadPromise = null;
                        throw err;
                    });
            }

            return window.faceApiModelsLoadPromise;
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

        function capturePhoto(videoElement, maxWidth = 640, quality = 0.8) {
            const naturalWidth = videoElement.videoWidth;
            const naturalHeight = videoElement.videoHeight;
            const scale = Math.min(1, maxWidth / naturalWidth);
            const width = Math.round(naturalWidth * scale);
            const height = Math.round(naturalHeight * scale);

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(videoElement, 0, 0, width, height);
            return canvas.toDataURL('image/jpeg', quality);
        }

        // ─── 3. Capture face ────────────────────────────────────────────────
        async function captureFace() {
            if (isProcessing) return;
            isProcessing = true;
            captureBtn.disabled = true;
            captureBtn.textContent = 'Memproses...';
            hideError();

            try {
                // Show processing feedback immediately so the user knows work
                // is happening (not frozen) while detection runs.
                showProcessing();

                // Run face detection + landmarks + descriptor on a DOWNSCALED
                // frame for speed (see getDetectionCanvas).
                const detectInput = getDetectionCanvas();

                // Elapsed-time-aware messaging: if this detection takes longer
                // than 2s (older/slower phones), reassure the user.
                slowTimer = setTimeout(() => {
                    processingText.textContent = 'Masih memproses, mohon tunggu...';
                }, 2000);

                const result = await faceapi
                    .detectSingleFace(detectInput, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                clearTimeout(slowTimer);
                hideProcessing();

                if (!result) {
                    showError('Wajah tidak terdeteksi, coba lagi');
                    isProcessing = false;
                    captureBtn.disabled = false;
                    captureBtn.textContent = 'Ambil Foto';
                    return;
                }

                // Convert Float32Array descriptor to plain array for JSON
                const descriptor = Array.from(result.descriptor);

                // Capture frame as base64 JPEG via canvas at a reduced upload size.
                const base64Photo = capturePhoto(video, 640, 0.8);

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
                hideProcessing();
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
                // Store all 3 descriptors SEPARATELY (array of arrays), instead
                // of averaging them into one. Each descriptor is a Float32Array
                // of 128 floats. Keeping all 3 preserves the natural variation
                // in the registered face (angle/lighting), so check-in matching
                // can compare against multiple reference points and take the
                // best (minimum) distance.
                // ────────────────────────────────────────────────────────────
                const finalDescriptors = captures.map((cap) => cap.descriptor);

                // Use the last captured photo as the reference
                const finalPhoto = captures[captures.length - 1].photo;

                const token = localStorage.getItem('token');
                const response = await fetch(`${APP_BASE_URL}/api/face-registration`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        descriptor: JSON.stringify(finalDescriptors),
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
                    window.location.href = `${APP_BASE_URL}/dashboard`;
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

                // Fire-and-forget warm-up detection so the first real
                // user-triggered detection doesn't pay one-time init cost.
                warmUpDetection();
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