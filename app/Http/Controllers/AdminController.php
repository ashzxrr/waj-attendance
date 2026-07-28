<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        return view('admin.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD', 'admin');

        if ($request->username === $username && $request->password === $password) {
            $request->session()->put('is_admin', true);
            return redirect('/admin/dashboard');
        }

        return back()->with('error', 'Username atau password salah');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('is_admin');
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
}
