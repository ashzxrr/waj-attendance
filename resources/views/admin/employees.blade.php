<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Karyawan - WAJ Attendance</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
</head>
<body class="min-h-screen bg-[#FFFDF7] p-4 lg:p-6 text-[#111827]">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ url('/admin/dashboard') }}" class="text-sm text-amber-700 hover:text-amber-900">&larr; Dashboard</a>
                <h1 class="text-2xl font-bold mt-2">Daftar Karyawan</h1>
            </div>
            <form method="POST" action="{{ url('/admin/logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-white border border-amber-200 text-gray-700 text-sm rounded-2xl shadow-sm">Logout</button>
            </form>
        </div>

        @if (session('success'))
            <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ url('/admin/karyawan') }}" class="mb-4">
            <label for="search" class="sr-only">Cari karyawan</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari nama, PIN, atau NIK..." autocomplete="off" class="w-full h-[46px] px-4 bg-white border border-amber-200 rounded-2xl text-sm text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-300">
        </form>

        <div class="bg-white/80 border border-amber-100 rounded-[24px] overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#FFF7D6] border-b border-amber-100">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">PIN</th>
                            <th class="px-4 py-3 font-medium">NIK</th>
                            <th class="px-4 py-3 font-medium">Status Kode Absensi</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        @forelse ($employees as $employee)
                            <tr class="hover:bg-[#FFFDF7]">
                                <td class="px-4 py-3 font-medium">{{ $employee->nama }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $employee->pin }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $employee->nik ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($employee->kode_absensi_is_default)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Default belum diganti</span>
                                    @elseif (!is_null($employee->kode_absensi_is_default) && !$employee->kode_absensi_is_default)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Sudah diganti</span>
                                    @else
                                        <span class="text-gray-400">Belum ada akun</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ url('/admin/karyawan/' . rawurlencode($employee->pin) . '/reset-code') }}" onsubmit="return confirm('Reset kode absensi ' + @js($employee->nama) + ' ke default PIN?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold hover:bg-rose-100">Reset Kode</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada data karyawan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $employees->links() }}</div>
        </div>
    </div>
    <script>
        const employeeSearch = document.getElementById('search');
        let searchTimer;

        employeeSearch.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => this.form.submit(), 350);
        });
    </script>
</body>
</html>