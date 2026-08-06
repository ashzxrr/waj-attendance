<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/admin/dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt([
            'username' => $request->username,
            'password' => $request->password,
        ])) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()->with('error', 'Username atau password salah');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    /**
     * Show admin dashboard with attendance data.
     */
    public function dashboard(Request $request)
    {
        $query = DB::table('attendance_logs')
            ->join('employees_cache', 'attendance_logs.pin', '=', 'employees_cache.pin')
            ->select(
                'attendance_logs.*',
                'employees_cache.nama',
                'employees_cache.nik'
            )
            ->orderBy('attendance_logs.datetime', 'desc');

        // ─── Filters ───────────────────────────────────────────────────

        if ($request->filled('tanggal')) {
            $query->where('attendance_logs.tanggal', $request->tanggal);
        }

        if ($request->filled('status')) {
            $query->where('attendance_logs.status', $request->status);
        }

        if ($request->filled('pin')) {
            $search = $request->pin;
            $query->where(function ($q) use ($search) {
                $q->where('attendance_logs.pin', 'like', "%{$search}%")
                  ->orWhere('employees_cache.nama', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // ─── Today's Summary Stats ─────────────────────────────────────

        $today = now()->format('Y-m-d');

        $stats = (object) [
            'total_today' => DB::table('attendance_logs')
                ->where('tanggal', $today)
                ->count(),

            'flagged_today' => DB::table('attendance_logs')
                ->where('tanggal', $today)
                ->where('status', 'flagged')
                ->count(),

            'unique_employees_today' => DB::table('attendance_logs')
                ->where('tanggal', $today)
                ->distinct('pin')
                ->count('pin'),
        ];

        return view('admin.dashboard', compact('logs', 'stats'));
    }

    /**
     * Approve a flagged attendance record.
     */
    public function approveAttendance(Request $request, $id)
    {
        $log = AttendanceLog::findOrFail($id);

        if ($log->status !== 'flagged') {
            return back()->with('error', 'Hanya dapat menyetujui absensi dengan status flagged');
        }

        $log->update([
            'status' => 'verified',
            'reviewed_by' => Auth::guard('admin')->user()->username,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Absensi disetujui, akan disinkronkan ke HRIS');
    }

    /**
     * Reject a flagged attendance record.
     */
    public function rejectAttendance(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $log = AttendanceLog::findOrFail($id);

        if ($log->status !== 'flagged') {
            return back()->with('error', 'Hanya dapat menolak absensi dengan status flagged');
        }

        $log->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::guard('admin')->user()->username,
            'reviewed_at' => now(),
            'review_note' => $request->reason,
        ]);

        return back()->with('success', 'Absensi ditolak');
    }
}
