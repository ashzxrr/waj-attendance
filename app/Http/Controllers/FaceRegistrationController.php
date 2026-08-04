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

            // Decode and validate descriptor.
            // Since registration captures 3 photos from slightly different angles,
            // face_embedding now stores an array of 3 descriptor arrays (each 128
            // floats). Keeping all 3 separately (instead of averaging) preserves
            // the natural variation in the registered face (angle/lighting), which
            // gives the matching logic more robust reference points at check-in.
            $descriptors = json_decode($request->descriptor, true);
            $valid = is_array($descriptors)
                && count($descriptors) === 3
                && collect($descriptors)->every(fn ($d) => is_array($d) && count($d) === 128);

            if (! $valid) {
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
            // face_embedding stores the array of 3 descriptor arrays as JSON.
            EmployeeFaceProfile::updateOrCreate(
                ['pin' => $pin],
                [
                    'face_embedding' => json_encode($descriptors),
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