<nav class="fixed inset-x-0 bottom-0 border-t border-slate-700/80 bg-slate-950/95 backdrop-blur-xl z-30">
    <div class="max-w-lg mx-auto px-4 py-3">
        <div class="grid grid-cols-3 gap-2">
            @php $path = request()->path(); @endphp

            <a href="/dashboard" class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-sm font-medium transition-all {{ $path === 'dashboard' ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-400 hover:bg-slate-800/80 hover:text-slate-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="/absen" class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-sm font-medium transition-all {{ $path === 'absen' ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-400 hover:bg-slate-800/80 hover:text-slate-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Absen</span>
            </a>

            <a href="/profile" class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2 text-sm font-medium transition-all {{ $path === 'profile' ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-400 hover:bg-slate-800/80 hover:text-slate-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M4 21v-2a4 4 0 0 1 3-3.87"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Profile</span>
            </a>
        </div>
    </div>
</nav>
