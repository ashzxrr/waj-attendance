import React from 'react';
import { Link, useLocation } from 'react-router-dom';

function BottomNav() {
    const location = useLocation();
    const isActive = (path) => location.pathname === path;

    return (
        <div className="fixed bottom-0 left-0 right-0 bg-slate-800/95 backdrop-blur-sm border-t border-slate-700/50 z-40">
            <div className="max-w-lg mx-auto">
                <div className="flex items-center justify-around py-2">
                    <Link
                        to="/app/riwayat"
                        className={`flex flex-col items-center gap-1 px-4 py-2 rounded-xl transition-all ${
                            isActive('/app/riwayat')
                                ? 'text-emerald-400 bg-emerald-500/10'
                                : 'text-slate-400 hover:text-slate-300'
                        }`}
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span className="text-xs font-medium">Riwayat</span>
                    </Link>

                    <Link
                        to="/app/absen"
                        className={`flex flex-col items-center gap-1 px-4 py-2 rounded-xl transition-all ${
                            isActive('/app/absen')
                                ? 'text-emerald-400 bg-emerald-500/10'
                                : 'text-slate-400 hover:text-slate-300'
                        }`}
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span className="text-xs font-medium">Absen</span>
                    </Link>

                    {/* Future navigation items */}
                    {/* <Link
                        to="/app/profile"
                        className={`flex flex-col items-center gap-1 px-4 py-2 rounded-xl transition-all ${
                            isActive('/app/profile')
                                ? 'text-emerald-400 bg-emerald-500/10'
                                : 'text-slate-400 hover:text-slate-300'
                        }`}
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span className="text-xs font-medium">Profile</span>
                    </Link> */}
                </div>
            </div>
        </div>
    );
}

export default BottomNav;
