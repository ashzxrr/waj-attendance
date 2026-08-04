<?php

namespace App\Http\Controllers;

use App\Models\EmployeeFaceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $auth = $request->user();
        $employee = $auth->employee;

        if (!$employee) {
            return response()->json([
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'pin' => $employee->pin,
            'nama' => $employee->nama,
            'nik' => $employee->nik,
            'last_login_at' => $auth->last_login_at,
            'device_id' => $auth->device_id,
            'face_registered' => EmployeeFaceProfile::where('pin', $auth->pin)->exists(),
        ]);
    }

    public function changePin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_pin_absensi' => ['required', 'string', 'digits:6'],
            'new_pin_absensi' => ['required', 'string', 'confirmed', 'digits:6'],
        ]);

        $auth = $request->user();

        if (!Hash::check($validated['current_pin_absensi'], $auth->pin_absensi)) {
            return response()->json([
                'message' => 'Kode absensi lama salah',
                'errors' => ['current_pin_absensi' => ['Kode absensi lama salah']],
            ], 422);
        }

        $auth->update([
            'pin_absensi' => $validated['new_pin_absensi'],
        ]);

        return response()->json([
            'message' => 'PIN absensi berhasil diubah.',
        ]);
    }
}
