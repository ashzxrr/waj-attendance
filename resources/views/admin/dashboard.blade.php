<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WAJ Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
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
            background: #1e293b;
            color: #e2e8f0;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            white-space: normal;
            max-width: 280px;
            z-index: 50;
            border: 1px solid #334155;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            pointer-events: none;
        }
    </style>
</head>
<body class="p-4 lg:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- ─── Header ──────────────────────────────────────────────────── -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
                <p class="text-slate-400 text-sm">Rekap absensi karyawan</p>
            </div>
            <form method="POST" action="/admin/logout">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm rounded-xl transition-colors">
                    Logout
                </button>
            </form>
        </div>

        <!-- ─── Summary Cards ────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-5 shadow-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-blue-500/10">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Absen Hari Ini</p>
                        <p class="text-2xl font-bold text-white">{{ $stats->total_today }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-5 shadow-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-yellow-500/10">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Flagged Hari Ini</p>
                        <p class="text-2xl font-bold text-white">{{ $stats->flagged_today }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-5 shadow-xl border border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-emerald-500/10">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wider">Karyawan Hari Ini</p>
                        <p class="text-2xl font-bold text-white">{{ $stats->unique_employees_today }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Filter Bar ──────────────────────────────────────────────── -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50 mb-6">
            <form method="GET" action="/admin/dashboard" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label for="tanggal" class="block text-xs text-slate-400 mb-1">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}"
                           class="px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <div>
                    <label for="status" class="block text-xs text-slate-400 mb-1">Status</label>
                    <select id="status" name="status"
                            class="px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="verified" @selected(request('status') === 'verified')>Verified</option>
                        <option value="flagged" @selected(request('status') === 'flagged')>Flagged</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label for="pin" class="block text-xs text-slate-400 mb-1">Cari PIN / Nama</label>
                    <input type="text" id="pin" name="pin" value="{{ request('pin') }}"
                           placeholder="Cari PIN atau nama..."
                           class="w-full px-3 py-2 bg-slate-700/50 border border-slate-600 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium rounded-lg transition-colors">
                    Filter
                </button>
                @if (request()->anyFilled(['tanggal', 'status', 'pin']))
                    <a href="/admin/dashboard"
                       class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- ─── Attendance Table ─────────────────────────────────────────── -->
        <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-700/50 overflow-hidden">
            <div class="table-wrap">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-700/50 bg-slate-800/90">
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">PIN</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Jam</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Jarak</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Face Score</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Foto</th>
                            <th class="px-4 py-3 text-slate-400 font-medium text-xs uppercase tracking-wider">Device</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 text-white font-medium">{{ $log->nama }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $log->pin }}</td>
                                <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ $log->tanggal }}</td>
                                <td class="px-4 py-3 text-slate-300 whitespace-nowrap">
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
                                            'verified' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'flagged' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                            'pending' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                        ];
                                        $color = $statusColors[$log->status] ?? $statusColors['pending'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-300 whitespace-nowrap">
                                    @if ($log->distance_from_office !== null)
                                        {{ number_format($log->distance_from_office, 1) }} m
                                        @if ($log->is_within_geofence)
                                            <span class="text-emerald-400 text-xs ml-1">✓</span>
                                        @else
                                            <span class="text-red-400 text-xs ml-1">✗</span>
                                        @endif
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-300">
                                    @if ($log->face_match_score !== null)
                                        {{ number_format($log->face_match_score * 100, 0) }}%
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->photo_path)
                                        @php
                                            $photoUrl = \Illuminate\Support\Facades\Storage::url($log->photo_path);
                                        @endphp
                                        <a href="{{ $photoUrl }}" target="_blank">
                                            <img src="{{ $photoUrl }}"
                                                 alt="Foto absen"
                                                 class="photo-thumb w-10 h-10 rounded-lg object-cover border border-slate-600">
                                        </a>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 max-w-[150px]">
                                    @if ($log->device_info)
                                        <span class="truncate-tooltip text-slate-300 text-xs block truncate"
                                              data-full="{{ e($log->device_info) }}">
                                            {{ $log->device_info }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center text-slate-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="px-4 py-4 border-t border-slate-700/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; {{ date('Y') }} WAJ Attendance System
        </p>
    </div>
</body>
</html>
