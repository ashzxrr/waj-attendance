<?php

namespace App\Http\Controllers;

use App\Models\EmployeeFaceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    public function show()
    {
        return view('face.register');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'descriptor' => ['required', 'json'],
                'photo' => ['required', 'string'],
            ]);

            /** @var \App\Models\EmployeeAuth $user */
            $user = $request->user();
            $pin = $user->pin;

            // Decode and validate descriptor (must be array of 128 floats)
            $descriptor = json_decode($request->descriptor, true);
            if (! is_array($descriptor) || count($descriptor) !== 128) {
                return response()->json([
                    'message' => 'Format descriptor wajah tidak valid.',
                ], 422);
            }

            // Decode base64 photo
            $photoData = $request->photo;
            if (str_starts_with($photoData, 'data:image')) {
                $photoData = substr($photoData, strpos($photoData, ',') + 1);
            }
            $photoData = base64_decode($photoData);
            if ($photoData === false) {
                return response()->json([
                    'message' => 'Format foto tidak valid.',
                ], 422);
            }

            // Save photo to storage
            $timestamp = now()->format('Ymd_His');
            $photoPath = "face-references/{$pin}-{$timestamp}.jpg";
            Storage::disk('public')->put($photoPath, $photoData);

            // Upsert face profile
            EmployeeFaceProfile::updateOrCreate(
                ['pin' => $pin],
                [
                    'face_embedding' => json_encode($descriptor),
                    'photo_reference_path' => $photoPath,
                    'registered_at' => now(),
                ]
            );

            return response()->json([
                'message' => 'Registrasi wajah berhasil!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}