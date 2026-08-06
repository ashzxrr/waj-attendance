<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Absensi - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    @include('partials.base-url-meta')
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="p-4">
    <div class="max-w-lg mx-auto pb-28">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-white">Riwayat Absensi</h1>
                <p class="text-slate-400 text-sm" id="employeeName">Karyawan</p>
            </div>
            <button id="logoutBtn" class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm rounded-xl transition-colors">
                Logout
            </button>
        </div>

        <!-- Month Selector -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50 mb-6">
            <div class="flex items-center justify-between">
                <button id="prevMonthBtn" class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div class="text-center">
                    <p id="currentMonthLabel" class="text-white font-semibold text-lg">-</p>
                    <p class="text-slate-400 text-xs">Bulan ini</p>
                </div>
                <button id="nextMonthBtn" class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-emerald-500/10">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs">Hadir</p>
                        <p id="totalHadir" class="text-white font-bold text-xl">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-amber-500/10">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs">Direview</p>
                        <p id="totalFlagged" class="text-white font-bold text-xl">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-slate-700/50 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-700/50 mb-4">
                <svg class="w-6 h-6 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Memuat riwayat absensi...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-slate-700/50 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700/50 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">Belum ada riwayat absensi bulan ini</p>
        </div>

        <!-- Attendance List -->
        <div id="attendanceList" class="hidden space-y-3">
            <!-- Records will be inserted here -->
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="hidden mt-6 flex justify-center gap-2">
            <button id="prevPageBtn" class="px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Sebelumnya
            </button>
            <span id="pageInfo" class="px-4 py-2 text-slate-400 text-sm">-</span>
            <button id="nextPageBtn" class="px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Selanjutnya
            </button>
        </div>
    </div>

    <!-- Photo Modal -->
    <div id="photoModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="relative max-w-4xl max-h-[90vh] w-full mx-4">
            <button onclick="closePhotoModal()" class="absolute -top-10 right-0 text-white hover:text-emerald-400 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="modalImage" src="" alt="Foto absen" class="w-full h-full object-contain rounded-lg">
        </div>
    </div>

    @include('partials.bottom-nav')

    <script>
        const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]').content.replace(/\/$/, '');
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = `${APP_BASE_URL}/login`;
        }

        // State
        let currentMonth = new Date();
        let currentPage = 1;
        let lastPage = 1;

        // DOM Elements
        const employeeName = document.getElementById('employeeName');
        const currentMonthLabel = document.getElementById('currentMonthLabel');
        const prevMonthBtn = document.getElementById('prevMonthBtn');
        const nextMonthBtn = document.getElementById('nextMonthBtn');
        const totalHadir = document.getElementById('totalHadir');
        const totalFlagged = document.getElementById('totalFlagged');
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const attendanceList = document.getElementById('attendanceList');
        const paginationContainer = document.getElementById('paginationContainer');
        const prevPageBtn = document.getElementById('prevPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('pageInfo');
        const logoutBtn = document.getElementById('logoutBtn');

        // Initialize
        employeeName.textContent = localStorage.getItem('employee_name') || 'Karyawan';
        updateMonthLabel();
        loadHistory();

        // Event Listeners
        prevMonthBtn.addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            currentPage = 1;
            updateMonthLabel();
            loadHistory();
        });

        nextMonthBtn.addEventListener('click', () => {
            const now = new Date();
            if (currentMonth.getMonth() < now.getMonth() || currentMonth.getFullYear() < now.getFullYear()) {
                currentMonth.setMonth(currentMonth.getMonth() + 1);
                currentPage = 1;
                updateMonthLabel();
                loadHistory();
            }
        });

        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadHistory();
            }
        });

        nextPageBtn.addEventListener('click', () => {
            if (currentPage < lastPage) {
                currentPage++;
                loadHistory();
            }
        });

        logoutBtn.addEventListener('click', async function() {
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

        function updateMonthLabel() {
            const options = { month: 'long', year: 'numeric' };
            currentMonthLabel.textContent = currentMonth.toLocaleDateString('id-ID', options);
            
            // Disable next month button if we're on current month
            const now = new Date();
            nextMonthBtn.disabled = currentMonth.getMonth() === now.getMonth() && currentMonth.getFullYear() === now.getFullYear();
        }

        async function loadHistory() {
            const bulan = currentMonth.toISOString().slice(0, 7); // YYYY-MM format
            
            showLoading();
            
            try {
                const response = await fetch(`${APP_BASE_URL}/api/attendance/history?bulan=${bulan}&page=${currentPage}`, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat riwayat absensi.');
                }

                const data = await response.json();
                
                // Update summary
                totalHadir.textContent = data.summary.total_hari_hadir;
                totalFlagged.textContent = data.summary.total_flagged;

                // Update pagination state
                lastPage = data.pagination.last_page;
                currentPage = data.pagination.current_page;

                // Render attendance list
                renderAttendanceList(data.logs);

                // Update pagination controls
                updatePagination(data.pagination);

            } catch (error) {
                console.error('Error loading history:', error);
                showError();
            }
        }

        function renderAttendanceList(logs) {
            attendanceList.innerHTML = '';
            
            if (logs.length === 0) {
                showEmpty();
                return;
            }

            // Group by date
            const groupedByDate = {};
            logs.forEach(log => {
                if (!groupedByDate[log.tanggal]) {
                    groupedByDate[log.tanggal] = [];
                }
                groupedByDate[log.tanggal].push(log);
            });

            // Render grouped records
            Object.keys(groupedByDate).sort().reverse().forEach(date => {
                const dayRecords = groupedByDate[date];
                const dateCard = createDateCard(date, dayRecords);
                attendanceList.appendChild(dateCard);
            });

            showList();
        }

        function createDateCard(date, records) {
            const card = document.createElement('div');
            card.className = 'bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50';

            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long' 
            });

            let html = `
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-white font-semibold">${formattedDate}</h3>
                </div>
                <div class="space-y-2">
            `;

            records.forEach(record => {
                const typeLabel = record.type === 'IN' ? 'Masuk' : 'Pulang';
                const typeColor = record.type === 'IN' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                const statusColor = record.status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                const statusLabel = record.status === 'verified' ? 'Terverifikasi' : 'Menunggu Review';

                html += `
                    <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium border ${typeColor}">
                                ${typeLabel}
                            </span>
                            <span class="text-white font-medium">${record.jam}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium border ${statusColor}">
                                ${statusLabel}
                            </span>
                            ${record.photo_path ? `
                                <img src="${record.photo_path}" 
                                     alt="Foto absen" 
                                     class="w-10 h-10 rounded-lg object-cover border border-slate-600 cursor-pointer hover:border-emerald-500/50 transition-colors"
                                     onclick="openPhotoModal('${record.photo_path}')">
                            ` : `
                                <div class="w-10 h-10 rounded-lg bg-slate-700/50 border border-slate-600 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            `}
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            card.innerHTML = html;

            return card;
        }

        function updatePagination(pagination) {
            if (pagination.last_page > 1) {
                paginationContainer.classList.remove('hidden');
                pageInfo.textContent = `Halaman ${pagination.current_page} dari ${pagination.last_page}`;
                prevPageBtn.disabled = pagination.current_page <= 1;
                nextPageBtn.disabled = pagination.current_page >= pagination.last_page;
            } else {
                paginationContainer.classList.add('hidden');
            }
        }

        function showLoading() {
            loadingState.classList.remove('hidden');
            emptyState.classList.add('hidden');
            attendanceList.classList.add('hidden');
            paginationContainer.classList.add('hidden');
        }

        function showEmpty() {
            loadingState.classList.add('hidden');
            emptyState.classList.remove('hidden');
            attendanceList.classList.add('hidden');
            paginationContainer.classList.add('hidden');
        }

        function showList() {
            loadingState.classList.add('hidden');
            emptyState.classList.add('hidden');
            attendanceList.classList.remove('hidden');
        }

        function showError() {
            loadingState.classList.add('hidden');
            emptyState.classList.remove('hidden');
            emptyState.querySelector('p').textContent = 'Gagal memuat riwayat absensi. Silakan coba lagi.';
            attendanceList.classList.add('hidden');
            paginationContainer.classList.add('hidden');
        }

        function openPhotoModal(photoUrl) {
            document.getElementById('modalImage').src = photoUrl;
            document.getElementById('photoModal').classList.remove('hidden');
            document.getElementById('photoModal').classList.add('flex');
        }

        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
            document.getElementById('photoModal').classList.remove('flex');
            document.getElementById('modalImage').src = '';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePhotoModal();
            }
        });

        // Close modal on backdrop click
        document.getElementById('photoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePhotoModal();
            }
        });
    </script>
</body>
</html>
