<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(251,191,36,0.18), transparent 35%),
                radial-gradient(circle at bottom left, rgba(253,230,138,0.18), transparent 30%),
                #FFFDF7;
            min-height: 100vh;
            color: #111827;
        }
        /* Scrollable table on small screens */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        /* Photo thumbnail lightbox */
        .photo-thumb {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .photo-thumb:hover {
            transform: scale(1.1);
        }
        /* Tooltip for truncated text */
        .truncate-tooltip {
            position: relative;
        }
        .truncate-tooltip:hover::after {
            content: attr(data-full);
            position: absolute;
            bottom: 100%;
            left: 0;
            background: #111827;
            color: #fff7d6;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            white-space: normal;
            max-width: 280px;
            z-index: 50;
            border: 1px solid #f5d97a;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            pointer-events: none;
        }
    </style>
</head>
<body class="min-h-screen p-4 lg:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- ─── Header ──────────────────────────────────────────────────── -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#111827]">Admin Dashboard</h1>
                <p class="text-gray-500 text-sm">Rekap absensi karyawan</p>
            </div>
            <form method="POST" action="{{ url('/admin/logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-white border border-amber-200 hover:bg-[#FFF7D6] text-gray-700 text-sm rounded-2xl shadow-sm transition-colors">
                    Logout
                </button>
            </form>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl">
                <p class="text-emerald-700 text-sm">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 rounded-2xl">
                <p class="text-rose-700 text-sm">{{ session('error') }}</p>
            </div>
        @endif

        <!-- ─── Summary Cards ────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="rounded-[24px] border border-amber-100 bg-white/80 p-5 shadow-[0_20px_60px_rgba(180,140,30,0.10)] backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-[#FFF7D6] border border-amber-200">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Absen Hari Ini</p>
                        <p class="text-2xl font-bold text-[#111827]">{{ $stats->total_today }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[24px] border border-amber-100 bg-white/80 p-5 shadow-[0_20px_60px_rgba(180,140,30,0.10)] backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-[#FFF7D6] border border-amber-200">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Flagged Hari Ini</p>
                        <p class="text-2xl font-bold text-[#111827]">{{ $stats->flagged_today }}</p>
                        <p class="text-gray-400 text-xs mt-1">Belum direview</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[24px] border border-amber-100 bg-white/80 p-5 shadow-[0_20px_60px_rgba(180,140,30,0.10)] backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-[#FFF7D6] border border-amber-200">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase tracking-wider">Karyawan Hari Ini</p>
                        <p class="text-2xl font-bold text-[#111827]">{{ $stats->unique_employees_today }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Filter Bar ──────────────────────────────────────────────── -->
        <div class="rounded-[24px] border border-amber-100 bg-white/80 p-4 shadow-[0_20px_60px_rgba(180,140,30,0.10)] backdrop-blur-sm mb-6">
            <form method="GET" action="{{ url('/admin/dashboard') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label for="tanggal" class="block text-xs text-gray-500 mb-1">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}"
                           class="px-3 py-2 bg-[#FFFDF7] border border-amber-200 rounded-2xl text-[#111827] text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <div>
                    <label for="status" class="block text-xs text-gray-500 mb-1">Status</label>
                    <select id="status" name="status"
                            class="px-3 py-2 bg-[#FFFDF7] border border-amber-200 rounded-2xl text-[#111827] text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="verified" @selected(request('status') === 'verified')>Verified</option>
                        <option value="flagged" @selected(request('status') === 'flagged')>Flagged</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label for="pin" class="block text-xs text-gray-500 mb-1">Cari PIN / Nama</label>
                    <input type="text" id="pin" name="pin" value="{{ request('pin') }}"
                           placeholder="Cari PIN atau nama..."
                           class="w-full px-3 py-2 bg-[#FFFDF7] border border-amber-200 rounded-2xl text-[#111827] text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white text-sm font-medium rounded-2xl transition-colors">
                    Filter
                </button>
                @if (request()->anyFilled(['tanggal', 'status', 'pin']))
                    <a href="{{ url('/admin/dashboard') }}"
                       class="px-4 py-2 bg-white border border-amber-200 hover:bg-[#FFF7D6] text-gray-700 text-sm font-medium rounded-2xl transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- ─── Attendance Table ─────────────────────────────────────────── -->
        <div class="rounded-[24px] border border-amber-100 bg-white/80 shadow-[0_20px_60px_rgba(180,140,30,0.10)] backdrop-blur-sm overflow-hidden">
            <div class="table-wrap">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-amber-100 bg-[#FFFDF7]">
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">PIN</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Jam</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Jarak</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Face Score</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Foto</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Device</th>
                            <th class="px-4 py-3 text-gray-500 font-medium text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-[#FFFDF7] transition-colors">
                                <td class="px-4 py-3 text-[#111827] font-medium">{{ $log->nama }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $log->pin }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $log->tanggal }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log->datetime)->format('H:i:s') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->type === 'IN')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            IN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            OUT
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'flagged' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'pending' => 'bg-[#FFF7D6] text-amber-700 border-amber-200',
                                            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                        $color = $statusColors[$log->status] ?? $statusColors['pending'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                    @if ($log->reviewed_by)
                                        <span class="ml-2 text-xs text-gray-400" title="Direview oleh {{ $log->reviewed_by }}">
                                            ({{ $log->reviewed_by }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    @if ($log->distance_from_office !== null)
                                        {{ number_format($log->distance_from_office, 1) }} m
                                        @if ($log->is_within_geofence)
                                            <span class="text-emerald-600 text-xs ml-1">✓</span>
                                        @else
                                            <span class="text-rose-600 text-xs ml-1">✗</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if ($log->face_match_score !== null)
                                        {{ number_format($log->face_match_score * 100, 0) }}%
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->photo_path)
                                        @php
                                            $photoUrl = asset('storage/' . ltrim($log->photo_path, '/'));
                                        @endphp
                                        <a href="{{ $photoUrl }}" target="_blank">
                                            <img src="{{ $photoUrl }}"
                                                 alt="Foto absen"
                                                 class="photo-thumb w-10 h-10 rounded-xl object-cover border border-amber-200">
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 max-w-[150px]">
                                    @if ($log->device_info)
                                        <span class="truncate-tooltip text-gray-600 text-xs block truncate"
                                              data-full="{{ e($log->device_info) }}">
                                            {{ $log->device_info }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->status === 'flagged')
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ url('/admin/attendance/' . $log->id . '/approve') }}" onsubmit="return confirm('Yakin absensi ini valid?');">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-2xl transition-colors">
                                                    Setujui
                                                </button>
                                            </form>
                                            <button onclick="openRejectModal({{ $log->id }})" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-medium rounded-2xl transition-colors">
                                                Tolak
                                            </button>
                                        </div>
                                    @elseif (in_array($log->status, ['verified', 'rejected']))
                                        @if ($log->reviewed_by)
                                            <span class="text-xs text-gray-400" title="Direview oleh {{ $log->reviewed_by }}">
                                                Direview oleh {{ $log->reviewed_by }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p>Tidak ada data absensi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ─── Pagination ─────────────────────────────────────────── -->
            @if ($logs->hasPages())
                <div class="px-4 py-4 border-t border-amber-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-[#FFFDF7]/80 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="rounded-[24px] border border-amber-100 bg-white p-6 max-w-md w-full mx-4 shadow-[0_20px_60px_rgba(180,140,30,0.14)]">
            <h3 class="text-lg font-semibold text-[#111827] mb-4">Tolak Absensi</h3>
            <form method="POST" id="rejectForm" action="">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm text-gray-600 mb-2">Alasan penolakan</label>
                    <textarea id="reason" name="reason" rows="3" required
                              class="w-full px-3 py-2 bg-[#FFFDF7] border border-amber-200 rounded-2xl text-[#111827] text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-white border border-amber-200 hover:bg-[#FFF7D6] text-gray-700 text-sm font-medium rounded-2xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-sm font-medium rounded-2xl transition-colors">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id) {
            document.getElementById('rejectForm').action = '{{ url('/admin/attendance') }}/' + id + '/reject';
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
            document.getElementById('reason').value = '';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectModal();
            }
        });

        // Close modal on backdrop click
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
