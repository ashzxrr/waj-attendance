<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Absen - WAJ Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.base-url-meta')
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .video-wrapper {
            position: relative;
            display: inline-block;
            max-width: 360px;
            width: 100%;
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
<body class="flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-sm pb-28">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 shadow-lg shadow-emerald-500/30 mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Absensi</h1>
            <p class="text-slate-400 text-sm mt-1" id="typeLabel">Memuat...</p>
        </div>

        <!-- Main Card -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-5 shadow-xl border border-slate-700/50">

            <!-- Loading State -->
            <div id="loadingState" class="text-center py-8">
                <div class="loading-spinner"></div>
                <p class="text-slate-300 text-sm">Memuat sistem absensi...</p>
                <p class="text-slate-500 text-xs mt-1">Mengunduh model dan data referensi</p>
            </div>

            <!-- Camera & Capture UI -->
            <div id="cameraUi" class="hidden space-y-4">
                <!-- Info: type + location -->
                <div id="infoBar" class="bg-slate-700/50 rounded-xl p-3 text-sm text-slate-300 text-center hidden">
                </div>

                <!-- Video -->
                <div class="flex justify-center">
                    <div class="video-wrapper">
                        <video id="video" autoplay playsinline></video>
                        <!-- Processing overlay: shown while detection is running -->
                        <div id="processingOverlay" class="hidden absolute inset-0 flex flex-col items-center justify-center rounded-2xl bg-slate-950/60 backdrop-blur-sm z-10">
                            <div class="w-10 h-10 border-4 border-emerald-500/30 border-t-emerald-400 rounded-full animate-spin mb-2"></div>
                            <p id="processingText" class="text-emerald-300 text-sm font-medium">Mendeteksi wajah...</p>
                        </div>
                    </div>
                </div>

                <!-- Face Mismatch Persistent Alert -->
                <div id="faceMismatchAlert" class="hidden bg-red-500/15 border-2 border-red-500/40 rounded-xl p-4 text-center">
                    <svg class="w-10 h-10 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-300 text-sm font-medium">Wajah tidak dikenali</p>
                    <p class="text-red-400/80 text-xs mt-1">Pastikan wajah Anda terlihat jelas di kamera, pencahayaan cukup, lalu coba lagi.</p>
                </div>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-xl p-3 text-center">
                </div>

                <!-- Capture Button -->
                <button
                    id="captureBtn"
                    class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-semibold text-lg rounded-xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-500/40 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Ambil Absen
                </button>
            </div>

            <!-- Geolocation Error -->
            <div id="geoError" class="hidden text-center py-8">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-red-400 text-sm mb-3">Lokasi tidak dapat diakses.</p>
                <p class="text-slate-400 text-xs mb-4">Aktifkan GPS/lokasi dan izinkan akses lokasi.</p>
                <button onclick="location.reload()" class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-xl transition-colors">
                    Coba Lagi
                </button>
            </div>

            <!-- Camera Error -->
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

            <!-- Result Card (shown after submission) -->
            <div id="resultCard" class="hidden space-y-4">
                <div id="resultIcon" class="text-center"></div>
                <p id="resultMessage" class="text-center text-lg font-semibold"></p>
                <p id="resultDetail" class="text-center text-sm text-slate-400"></p>
                <div class="flex gap-3 pt-2">
                    <a id="backToDashboardLink" href="/dashboard" class="flex-1 py-3 bg-slate-700 hover:bg-slate-600 text-white text-center font-medium rounded-xl transition-colors">
                        Kembali ke Dashboard
                    </a>
                    <button onclick="location.reload()" class="flex-1 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-medium rounded-xl transition-colors">
                        Absen Lagi
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>

    @include('partials.bottom-nav')

    <script>
        // ─── Token guard ────────────────────────────────────────────────────
        const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]').content.replace(/\/$/, '');
        document.getElementById('backToDashboardLink').href = `${APP_BASE_URL}/dashboard`;
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = `${APP_BASE_URL}/login`;
        }

        // ─── State ──────────────────────────────────────────────────────────
        let videoStream = null;
        let isProcessing = false;
        // referenceDescriptors is an array of descriptor arrays (3 by default),
        // because registration now stores multiple face embeddings to handle the
        // natural variation in the registered face (angle/lighting).
        let referenceDescriptors = null;
        let officeLocation = null;
        let nextType = null;
        let currentPosition = null;

        // ─── DOM refs ───────────────────────────────────────────────────────
        const video = document.getElementById('video');
        const loadingState = document.getElementById('loadingState');
        const cameraUi = document.getElementById('cameraUi');
        const cameraError = document.getElementById('cameraError');
        const geoError = document.getElementById('geoError');
        const errorMsg = document.getElementById('errorMessage');
        const faceMismatchAlert = document.getElementById('faceMismatchAlert');
        const captureBtn = document.getElementById('captureBtn');
        const typeLabel = document.getElementById('typeLabel');
        const infoBar = document.getElementById('infoBar');
        const resultCard = document.getElementById('resultCard');

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

        // ─── Helper ─────────────────────────────────────────────────────────
        function showError(msg) {
            errorMsg.textContent = msg;
            errorMsg.classList.remove('hidden');
        }

        function hideError() {
            errorMsg.classList.add('hidden');
        }

        // ─── 1. Fetch reference data from API ──────────────────────────────
        async function fetchReferenceData() {
            const headers = {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
            };

            const [descriptorRes, officeRes, typeRes] = await Promise.all([
                fetch(`${APP_BASE_URL}/api/attendance/reference-descriptor`, { headers }),
                fetch(`${APP_BASE_URL}/api/attendance/office-location`, { headers }),
                fetch(`${APP_BASE_URL}/api/attendance/next-type`, { headers }),
            ]);

            if (!descriptorRes.ok) throw new Error('Data wajah referensi tidak ditemukan');
            if (!officeRes.ok) throw new Error('Data kantor tidak ditemukan');

            const descriptorData = await descriptorRes.json();
            const officeData = await officeRes.json();
            const typeData = await typeRes.json();

            referenceDescriptors = descriptorData.descriptor;

            // Backward compatibility: old registrations stored a single averaged
            // descriptor (a flat 128-length array); new registrations store an
            // array of 3 descriptor arrays. Normalize both to an array of arrays
            // so the matching loop below always iterates over multiple references.
            if (
                Array.isArray(referenceDescriptors)
                && referenceDescriptors.length > 0
                && typeof referenceDescriptors[0] === 'number'
            ) {
                referenceDescriptors = [referenceDescriptors];
            }

            officeLocation = officeData.office;
            nextType = typeData.next_type;

            const label = nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang';
            typeLabel.textContent = label;
            captureBtn.textContent = label;
        }

        // ─── 2. Load face-api.js models ────────────────────────────────────
        // ssdMobilenetv1 is used instead of tinyFaceDetector because it is
        // significantly more accurate at locating faces — critical for a
        // security-critical attendance system. It is heavier to load, so the
        // loading indicator stays visible while the weights download.
        async function loadModels() {
            const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
        }

        // ─── 3. Get geolocation ────────────────────────────────────────────
        function getGeolocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolokasi tidak didukung browser'));
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => resolve({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                    }),
                    (err) => {
                        let msg = 'Gagal mendapatkan lokasi.';
                        if (err.code === 1) msg = 'Akses lokasi ditolak. Izinkan akses lokasi di pengaturan browser.';
                        else if (err.code === 2) msg = 'Lokasi tidak tersedia.';
                        else if (err.code === 3) msg = 'Waktu permintaan lokasi habis.';
                        reject(new Error(msg));
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                );
            });
        }

        // ─── 4. Start camera ────────────────────────────────────────────────
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

        // ─── 5. Capture & verify face (multi-frame consensus) ──────────────
        // Verification policy constants:
        //   - FRAME_COUNT: number of live frames sampled per attempt.
        //   - FRAME_INTERVAL_MS: pause between captures so we sample slightly
        //     different moments (and micro-pose variations) of the same face.
        //   - MATCH_THRESHOLD: euclidean distance cutoff. Tightened from 0.6 to
        //     0.45 to reduce the false-accept rate (a different person must NOT
        //     pass). Tradeoff: genuine users may need good lighting/positioning
        //     to pass — acceptable for a security-critical attendance system.
        //   - REQUIRED_PASSES: consensus requirement — at least 2 of 3 frames
        //     must match. Multi-frame consensus prevents a single lucky/unlucky
        //     frame from deciding the result at check-in time.
        const FRAME_COUNT = 3;
        const FRAME_INTERVAL_MS = 500;
        const MATCH_THRESHOLD = 0.45;
        const REQUIRED_PASSES = 2;

        async function captureAttendance() {
            if (isProcessing) return;
            isProcessing = true;
            captureBtn.disabled = true;
            captureBtn.textContent = 'Memverifikasi...';
            hideError();
            faceMismatchAlert.classList.add('hidden');
            showProcessing();

            try {
                // ─── Step A: capture & score FRAME_COUNT consecutive frames ─
                const frameScores = [];
                for (let i = 0; i < FRAME_COUNT; i++) {
                    // Pause between captures so we sample slightly different moments
                    if (i > 0) await new Promise((r) => setTimeout(r, FRAME_INTERVAL_MS));

                    // ssdMobilenetv1 is more accurate than tinyFaceDetector,
                    // important for a security-critical attendance system.
                    // Detection runs on a DOWNSCALED frame for speed; the
                    // full-resolution frame is still used for the photo upload.
                    const detectInput = getDetectionCanvas();

                    // Elapsed-time-aware messaging: if this single detection
                    // attempt takes longer than 2s, reassure the user.
                    slowTimer = setTimeout(() => {
                        processingText.textContent = 'Masih memproses, mohon tunggu...';
                    }, 2000);

                    const result = await faceapi
                        .detectSingleFace(detectInput, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    clearTimeout(slowTimer);

                    if (!result) {
                        hideProcessing();
                        showError('Wajah tidak terdeteksi, coba lagi');
                        isProcessing = false;
                        captureBtn.disabled = false;
                        captureBtn.textContent = nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang';
                        return;
                    }

                    // ─── Score this frame ────────────────────────────────────
                    // Multiple reference descriptors handle the natural variation
                    // in the registered face (angle/lighting). Compare this
                    // frame's descriptor against ALL reference descriptors and
                    // keep the MINIMUM distance (best match) as the frame score.
                    let frameDistance = Infinity;
                    for (const refDescriptor of referenceDescriptors) {
                        const d = faceapi.euclideanDistance(result.descriptor, refDescriptor);
                        if (d < frameDistance) frameDistance = d;
                    }
                    frameScores.push(frameDistance);
                }

                // ─── Step B: consensus check ───────────────────────────────
                // At least REQUIRED_PASSES of the frames must be below threshold.
                const passingScores = frameScores.filter((d) => d <= MATCH_THRESHOLD);
                if (passingScores.length < REQUIRED_PASSES) {
                    hideProcessing();
                    faceMismatchAlert.classList.remove('hidden');
                    // Keep camera running, just reset button for retry
                    isProcessing = false;
                    captureBtn.disabled = false;
                    captureBtn.textContent = nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang';
                    return;
                }

                // Use the average of the passing distances as the final score
                // sent to the backend.
                const finalScore = passingScores.reduce((sum, d) => sum + d, 0) / passingScores.length;

                // Capture frame as base64 JPEG (from the current live video)
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0);
                const photo = canvas.toDataURL('image/jpeg', 0.85);

                // Detection is done — hide the processing indicator before submit.
                hideProcessing();

                // ─── Submit to API ─────────────────────────────────────────
                const response = await fetch(`${APP_BASE_URL}/api/attendance/store`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                    },
                    body: JSON.stringify({
                        latitude: currentPosition.latitude,
                        longitude: currentPosition.longitude,
                        face_match_score: finalScore,
                        photo: photo,
                        device_info: navigator.userAgent,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    // face_mismatch = hard reject, show persistent alert
                    if (data.error === 'face_mismatch') {
                        faceMismatchAlert.classList.remove('hidden');
                        // Keep camera running, just reset button for retry
                        isProcessing = false;
                        captureBtn.disabled = false;
                        captureBtn.textContent = nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang';
                        return;
                    }
                    throw new Error(data.message || 'Absen gagal');
                }

                // Hide any previous face mismatch alert on success
                faceMismatchAlert.classList.add('hidden');

                // ─── Show result ───────────────────────────────────────────
                showResult(data);
            } catch (err) {
                showError(err.message);
                hideProcessing();
                isProcessing = false;
                captureBtn.disabled = false;
                captureBtn.textContent = nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang';
            }
        }

        // ─── 6. Show result ────────────────────────────────────────────────
        function showResult(data) {
            cameraUi.classList.add('hidden');
            resultCard.classList.remove('hidden');

            const typeLabel = data.type === 'IN' ? 'Absen Masuk' : 'Absen Pulang';
            const isSuccess = data.status === 'verified';
            const time = new Date(data.datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            document.getElementById('resultIcon').innerHTML = isSuccess
                ? '<svg class="w-16 h-16 text-emerald-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                : '<svg class="w-16 h-16 text-amber-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>';

            document.getElementById('resultMessage').textContent = data.message;
            document.getElementById('resultMessage').className = 'text-center text-lg font-semibold ' + (isSuccess ? 'text-emerald-400' : 'text-amber-400');

            document.getElementById('resultDetail').innerHTML = `
                ${typeLabel} — ${time}<br>
                Jarak: ${data.distance_from_office ? data.distance_from_office.toFixed(0) + ' m' : '-'} |
                Face: ${data.face_match_score ? data.face_match_score.toFixed(3) : '-'}
            `;
        }

        // ─── Cleanup ───────────────────────────────────────────────────────
        window.addEventListener('beforeunload', function () {
            if (videoStream) {
                videoStream.getTracks().forEach(t => t.stop());
            }
        });

        // ─── Init ──────────────────────────────────────────────────────────
        (async function init() {
            try {
                // Step 1: fetch reference data
                await fetchReferenceData();

                // Step 2: load face-api.js models
                await loadModels();

                // Step 3: get geolocation
                try {
                    currentPosition = await getGeolocation();
                } catch (geoErr) {
                    loadingState.classList.add('hidden');
                    geoError.querySelector('p:first-of-type').textContent = geoErr.message;
                    geoError.classList.remove('hidden');
                    return;
                }

                // Step 4: start camera
                await startCamera();

                // Show info bar
                infoBar.innerHTML = `
                    <span class="text-emerald-400 font-medium">${nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang'}</span>
                    &middot; Lokasi: ${currentPosition.latitude.toFixed(4)}, ${currentPosition.longitude.toFixed(4)}
                `;
                infoBar.classList.remove('hidden');

                // Show camera UI
                loadingState.classList.add('hidden');
                cameraUi.classList.remove('hidden');

                // Fire-and-forget warm-up detection so the first real
                // user-triggered detection doesn't pay one-time init cost.
                warmUpDetection();
            } catch (err) {
                if (!cameraError.classList.contains('hidden') || !geoError.classList.contains('hidden')) return;
                loadingState.innerHTML = `
                    <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-400 text-sm">${err.message}</p>
                `;
            }
        })();

        // ─── Event listeners ───────────────────────────────────────────────
        captureBtn.addEventListener('click', captureAttendance);
    </script>
</body>
</html>