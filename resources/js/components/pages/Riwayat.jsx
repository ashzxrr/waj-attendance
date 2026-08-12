import React, { useState, useEffect } from 'react';
import axios from 'axios';

function Riwayat() {
    const [loading, setLoading] = useState(true);
    const [logs, setLogs] = useState([]);
    const [summary, setSummary] = useState({ total_hari_hadir: 0, total_flagged: 0 });
    const [currentMonth, setCurrentMonth] = useState(new Date());
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [employeeName, setEmployeeName] = useState('');
    const [error, setError] = useState(null);

    // Get APP_BASE_URL from meta tag (consistent with Blade approach)
    const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]')?.content?.replace(/\/$/, '') || '';
    const token = localStorage.getItem('token');

    const checkFaceRegistration = async () => {
        try {
            const response = await axios.get(`${APP_BASE_URL}/api/me`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            if (!response.data.face_registered) {
                window.location.href = `${APP_BASE_URL}/app/face-registration`;
            }
        } catch (err) {
            console.error('Error checking face registration:', err);
            // Continue anyway, don't block the page
        }
    };

    useEffect(() => {
        // Check authentication
        if (!token) {
            window.location.href = `${APP_BASE_URL}/login`;
            return;
        }
 
        // Load employee name from localStorage
        setEmployeeName(localStorage.getItem('employee_name') || 'Karyawan');

        // Check face registration status (only on mount)
        checkFaceRegistration();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        // Load history when month or page changes
        if (token) {
            loadHistory();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [currentMonth, currentPage, token]);

    const loadHistory = async () => {
        setLoading(true);
        setError(null);

        try {
            const bulan = currentMonth.toISOString().slice(0, 7); // YYYY-MM format
            const response = await axios.get(
                `${APP_BASE_URL}/api/attendance/history?bulan=${bulan}&page=${currentPage}`,
                {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                }
            );

            setLogs(response.data.logs);
            setSummary(response.data.summary);
            setLastPage(response.data.pagination.last_page);
            setCurrentPage(response.data.pagination.current_page);
        } catch (err) {
            console.error('Error loading history:', err);
            setError('Gagal memuat riwayat absensi. Silakan coba lagi.');
        } finally {
            setLoading(false);
        }
    };

    const handlePrevMonth = () => {
        const newMonth = new Date(currentMonth);
        newMonth.setMonth(newMonth.getMonth() - 1);
        setCurrentMonth(newMonth);
        setCurrentPage(1);
    };

    const handleNextMonth = () => {
        const now = new Date();
        if (currentMonth.getMonth() < now.getMonth() || currentMonth.getFullYear() < now.getFullYear()) {
            const newMonth = new Date(currentMonth);
            newMonth.setMonth(newMonth.getMonth() + 1);
            setCurrentMonth(newMonth);
            setCurrentPage(1);
        }
    };

    const handlePrevPage = () => {
        if (currentPage > 1) {
            setCurrentPage(currentPage - 1);
        }
    };

    const handleNextPage = () => {
        if (currentPage < lastPage) {
            setCurrentPage(currentPage + 1);
        }
    };

    const formatMonth = (date) => {
        return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    };

    const formatJam = (jam) => {
        return jam; // Already in HH:MM:SS format from API
    };

    const formatDate = (tanggal) => {
        const date = new Date(tanggal);
        return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
    };

    const groupLogsByDate = (logs) => {
        const grouped = {};
        logs.forEach(log => {
            if (!grouped[log.tanggal]) {
                grouped[log.tanggal] = [];
            }
            grouped[log.tanggal].push(log);
        });
        return grouped;
    };

    const openPhotoModal = (photoUrl) => {
        // Simple implementation - can be enhanced with a proper modal
        window.open(photoUrl, '_blank');
    };

    if (loading) {
        return (
            <div className="p-4">
                <div className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-slate-700/50 text-center">
                    <div className="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-700/50 mb-4">
                        <svg className="w-6 h-6 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p className="text-slate-400 text-sm">Memuat riwayat absensi...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="p-4">
                <div className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-slate-700/50 text-center">
                    <p className="text-red-400 text-sm">{error}</p>
                    <button
                        onClick={loadHistory}
                        className="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-colors"
                    >
                        Coba Lagi
                    </button>
                </div>
            </div>
        );
    }

    const groupedLogs = groupLogsByDate(logs);
    const sortedDates = Object.keys(groupedLogs).sort().reverse();

    return (
        <div className="p-4">
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-xl font-bold text-white">Riwayat Absensi</h1>
                    <p className="text-slate-400 text-sm">{employeeName}</p>
                </div>
                <button
                    onClick={() => {
                        localStorage.removeItem('token');
                        localStorage.removeItem('employee_name');
                        localStorage.removeItem('employee_pin');
                        window.location.href = `${APP_BASE_URL}/login`;
                    }}
                    className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-xl transition-colors"
                >
                    Logout
                </button>
            </div>

            {/* Month Selector */}
            <div className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50 mb-6">
                <div className="flex items-center justify-between">
                    <button
                        onClick={handlePrevMonth}
                        className="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 transition-colors"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div className="text-center">
                        <p className="text-white font-semibold text-lg">{formatMonth(currentMonth)}</p>
                        <p className="text-slate-400 text-xs">Bulan ini</p>
                    </div>
                    <button
                        onClick={handleNextMonth}
                        disabled={currentMonth.getMonth() === new Date().getMonth() && currentMonth.getFullYear() === new Date().getFullYear()}
                        className="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-2 gap-4 mb-6">
                <div className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50">
                    <div className="flex items-center gap-3">
                        <div className="p-2 rounded-xl bg-emerald-500/10">
                            <svg className="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-slate-400 text-xs">Hadir</p>
                            <p className="text-white font-bold text-xl">{summary.total_hari_hadir}</p>
                        </div>
                    </div>
                </div>

                <div className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50">
                    <div className="flex items-center gap-3">
                        <div className="p-2 rounded-xl bg-amber-500/10">
                            <svg className="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-slate-400 text-xs">Direview</p>
                            <p className="text-white font-bold text-xl">{summary.total_flagged}</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Attendance List */}
            {sortedDates.length === 0 ? (
                <div className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-slate-700/50 text-center">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-700/50 mb-4">
                        <svg className="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p className="text-slate-400 text-sm">Belum ada riwayat absensi bulan ini</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {sortedDates.map(date => (
                        <div key={date} className="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-4 shadow-xl border border-slate-700/50">
                            <div className="flex items-center justify-between mb-3">
                                <h3 className="text-white font-semibold">{formatDate(date)}</h3>
                            </div>
                            <div className="space-y-2">
                                {groupedLogs[date].map(record => (
                                    <div key={record.id} className="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                                        <div className="flex items-center gap-3">
                                            <span className={`inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium border ${
                                                record.type === 'IN'
                                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                                    : 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                            }`}>
                                                {record.type === 'IN' ? 'Masuk' : 'Pulang'}
                                            </span>
                                            <span className="text-white font-medium">{formatJam(record.jam)}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className={`inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium border ${
                                                record.status === 'verified'
                                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                                    : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                            }`}>
                                                {record.status === 'verified' ? 'Terverifikasi' : 'Menunggu Review'}
                                            </span>
                                            {record.photo_path && (
                                                <img
                                                    src={record.photo_path}
                                                    alt="Foto absen"
                                                    className="w-10 h-10 rounded-lg object-cover border border-slate-600 cursor-pointer hover:border-emerald-500/50 transition-colors"
                                                    onClick={() => openPhotoModal(record.photo_path)}
                                                />
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Pagination */}
            {lastPage > 1 && (
                <div className="mt-6 flex justify-center gap-2">
                    <button
                        onClick={handlePrevPage}
                        disabled={currentPage <= 1}
                        className="px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Sebelumnya
                    </button>
                    <span className="px-4 py-2 text-slate-400 text-sm">
                        Halaman {currentPage} dari {lastPage}
                    </span>
                    <button
                        onClick={handleNextPage}
                        disabled={currentPage >= lastPage}
                        className="px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Selanjutnya
                    </button>
                </div>
            )}
        </div>
    );
}

export default Riwayat;
