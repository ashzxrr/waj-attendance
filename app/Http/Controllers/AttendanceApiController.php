<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\EmployeeFaceProfile;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceApiController extends Controller
{
    /**
     * Return the authenticated employee's face descriptor for client-side comparison.
     */
    public function getReferenceDescriptor(Request $request): JsonResponse
    {
        try {
            $pin = $request->user()->pin;

            $profile = EmployeeFaceProfile::where('pin', $pin)->first();

            if (! $profile || ! $profile->face_embedding) {
                return response()->json([
                    'message' => 'Profil wajah tidak ditemukan. Silakan registrasi wajah terlebih dahulu.',
                ], 404);
            }

            return response()->json([
                'descriptor' => json_decode($profile->face_embedding),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return office location (single office — first record).
     */
    public function getOfficeLocation(): JsonResponse
    {
        try {
            $office = Office::first();

            if (! $office) {
                return response()->json([
                    'message' => 'Data kantor belum dikonfigurasi.',
                ], 404);
            }

            return response()->json([
                'office' => [
                    'name' => $office->name,
                    'latitude' => (float) $office->latitude,
                    'longitude' => (float) $office->longitude,
                    'radius_meter' => (int) $office->radius_meter,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determine the next expected attendance type (IN or OUT) for today.
     */
    public function determineNextType(Request $request): JsonResponse
    {
        try {
            $pin = $request->user()->pin;
            $today = now()->toDateString();

            $lastLog = AttendanceLog::where('pin', $pin)
                ->where('tanggal', $today)
                ->orderBy('datetime', 'desc')
                ->first();

            $nextType = $lastLog && $lastLog->type === 'IN' ? 'OUT' : 'IN';

            return response()->json([
                'next_type' => $nextType,
                'last_type' => $lastLog?->type,
                'last_datetime' => $lastLog?->datetime,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store an attendance record.
     *
     * Face matching uses face-api.js euclidean distance:
     *   - Lower distance = better match
     *   - Threshold: distance <= 0.6 → face_verified = true
     *   - The raw euclidean distance is stored as face_match_score
     *
     * Geofence uses the Haversine formula to calculate great-circle distance
     * between the employee's GPS coordinate and the office coordinate.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'face_match_score' => ['required', 'numeric', 'min:0', 'max:2'],
                'photo' => ['required', 'string'],
                'device_info' => ['nullable', 'string'],
            ]);

            $pin = $request->user()->pin;
            $today = now()->toDateString();

            // ─── Determine type (IN / OUT) ────────────────────────────────
            $lastLog = AttendanceLog::where('pin', $pin)
                ->where('tanggal', $today)
                ->orderBy('datetime', 'desc')
                ->first();

            $type = $lastLog && $lastLog->type === 'IN' ? 'OUT' : 'IN';

            // ─── Face verification (HARD REJECT if mismatch) ───────────────
            // face-api.js euclidean distance: lower = better match.
            // Threshold 0.6 is a commonly used cutoff for face-api.js.
            //
            // Face mismatch = hard reject (security/fraud prevention).
            // We reject BEFORE geofence calculation to avoid wasting compute
            // and to keep attendance data clean — unverified faces should
            // never pollute the attendance_logs table.
            // Geofence mismatch = soft flag (legitimate edge cases like GPS
            // drift, still worth recording for HR review).
            // ────────────────────────────────────────────────────────────────
            $faceMatchScore = (float) $request->face_match_score;
            $faceVerified = $faceMatchScore <= 0.6;

            if (! $faceVerified) {
                Log::warning('Attendance face mismatch rejected', [
                    'pin' => $pin,
                    'timestamp' => now()->toIso8601String(),
                    'face_match_score' => $faceMatchScore,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'face_mismatch',
                    'message' => 'Wajah tidak dikenali. Pastikan wajah terlihat jelas dan pencahayaan cukup, lalu coba lagi.',
                ], 422);
            }

            // ─── Haversine distance calculation ────────────────────────────
            // Formula: a = sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlon/2)
            //          c = 2 · atan2(√a, √(1-a))
            //          d = R · c   where R = 6371000m (Earth's mean radius)
            //
            // Only computed after face passes — geofence mismatch is a soft
            // flag, not a hard reject.
            // ────────────────────────────────────────────────────────────────
            $office = Office::first();
            $distanceFromOffice = null;
            $isWithinGeofence = false;

            if ($office) {
                $lat1 = deg2rad((float) $request->latitude);
                $lon1 = deg2rad((float) $request->longitude);
                $lat2 = deg2rad((float) $office->latitude);
                $lon2 = deg2rad((float) $office->longitude);

                $dlat = $lat2 - $lat1;
                $dlon = $lon2 - $lon1;

                $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distanceFromOffice = round(6371000 * $c, 2); // Earth radius in meters

                $isWithinGeofence = $distanceFromOffice <= (int) $office->radius_meter;
            }

            // ─── Status ────────────────────────────────────────────────────
            // Face already verified above. Geofence determines if verified or flagged.
            $status = $isWithinGeofence ? 'verified' : 'flagged';

            // ─── Save photo ────────────────────────────────────────────────
            $photoData = $request->photo;
            if (str_starts_with($photoData, 'data:image')) {
                $photoData = substr($photoData, strpos($photoData, ',') + 1);
            }
            $photoData = base64_decode($photoData);

            $timestamp = now()->format('Ymd_His');
            $photoPath = "attendance-photos/{$pin}-{$timestamp}.jpg";
            Storage::disk('public')->put($photoPath, $photoData);

            // ─── Create attendance log ─────────────────────────────────────
            $log = AttendanceLog::create([
                'pin' => $pin,
                'datetime' => now(),
                'tanggal' => $today,
                'type' => $type,
                'photo_path' => $photoPath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'distance_from_office' => $distanceFromOffice,
                'is_within_geofence' => $isWithinGeofence,
                'face_match_score' => $faceMatchScore,
                'face_verified' => $faceVerified,
                'device_info' => $request->device_info,
                'status' => $status,
            ]);

            // ─── Response ──────────────────────────────────────────────────
            $typeLabel = $type === 'IN' ? 'Masuk' : 'Pulang';
            $message = "Absen {$typeLabel} berhasil";

            if ($status === 'flagged') {
                $message = 'Di luar area pabrik, absen ditandai untuk review';
            }

            return response()->json([
                'status' => $status,
                'type' => $type,
                'message' => $message,
                'datetime' => $log->datetime,
                'distance_from_office' => $distanceFromOffice,
                'is_within_geofence' => $isWithinGeofence,
                'face_verified' => $faceVerified,
                'face_match_score' => $faceMatchScore,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}