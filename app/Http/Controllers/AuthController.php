<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\EmployeeAuth;
use App\Models\EmployeeCache;
use App\Models\EmployeeFaceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $employee = EmployeeCache::where('pin', $validated['pin'])
                ->where('is_active', true)
                ->first();

            if (!$employee) {
                return response()->json([
                    'message' => 'PIN atau kode absensi salah',
                    'errors' => ['pin' => ['PIN atau kode absensi salah']],
                ], 422);
            }

            $auth = EmployeeAuth::where('pin', $validated['pin'])->first();

            if (!$auth || !Hash::check($validated['pin_absensi'], $auth->pin_absensi)) {
                return response()->json([
                    'message' => 'PIN atau kode absensi salah',
                    'errors' => ['pin_absensi' => ['PIN atau kode absensi salah']],
                ], 422);
            }

            // Revoke old tokens
            $auth->tokens()->delete();

            // Create new token
            $token = $auth->createToken('employee-token')->plainTextToken;

            // Update device binding and login timestamp
            $auth->update([
                'device_id' => $validated['device_id'] ?? $auth->device_id,
                'last_login_at' => now(),
            ]);

            return response()->json([
                'token' => $token,
                'employee' => [
                    'pin' => $employee->pin,
                    'nama' => $employee->nama,
                ],
                'face_registered' => EmployeeFaceProfile::where('pin', $employee->pin)->exists(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Berhasil logout.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $auth = $request->user();
            $employee = $auth->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Data karyawan tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'employee' => [
                    'pin' => $employee->pin,
                    'nama' => $employee->nama,
                    'nik' => $employee->nik,
                    'is_active' => $employee->is_active,
                ],
                'device_id' => $auth->device_id,
                'last_login_at' => $auth->last_login_at,
                'face_registered' => EmployeeFaceProfile::where('pin', $auth->pin)->exists(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkDeviceBinding(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'pin' => ['required', 'string'],
                'device_id' => ['required', 'string'],
            ]);

            $auth = EmployeeAuth::where('pin', $request->pin)->first();

            if (!$auth) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Data auth tidak ditemukan.',
                ], 404);
            }

            $deviceMismatch = $auth->device_id !== null && $auth->device_id !== $request->device_id;

            return response()->json([
                'exists' => true,
                'device_mismatch' => $deviceMismatch,
                'bound_device_id' => $auth->device_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}