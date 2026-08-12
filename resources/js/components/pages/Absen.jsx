import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useCameraContext } from '../../contexts/CameraContext.jsx';
import { useCamera } from '../../hooks/useCamera';
import { useFaceApi } from '../../hooks/useFaceApi';
import { useFaceCapture } from '../../hooks/useFaceCapture';
import { useGeolocation } from '../../hooks/useGeolocation';

function Absen() {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);
    const [nextType, setNextType] = useState('IN');
    const [lastType, setLastType] = useState(null);
    const [locationInfo, setLocationInfo] = useState(null);
    const [showCamera, setShowCamera] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);
    const [faceMismatch, setFaceMismatch] = useState(false);

    // Custom hooks
    const { isLoaded: faceApiLoaded, loadModels: loadFaceApiModels } = useFaceApi();
    const { stream, isReady: cameraReady, videoRef, startCamera, stopCamera } = useCamera();
    const { registerCameraPage, unregisterCameraPage } = useCameraContext();
    const { captureFace, capturePhoto, isProcessing } = useFaceCapture();
    const { location, getCurrentLocation } = useGeolocation();

    const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]')?.content?.replace(/\/$/, '') || '';
    const token = localStorage.getItem('token');

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        // Register this as a camera page 
        registerCameraPage();

        initializeApp();

        // Unregister when unmounting
        return () => {
            unregisterCameraPage();
        };
    }, [registerCameraPage, unregisterCameraPage]);

    const initializeApp = async () => {
        try {
            setLoading(true);

            // Check if camera is already warmed up from context
            if (stream) {
                const tracks = stream.getTracks();
                const isStreamActive = tracks.some(track => track.readyState === 'live');
                
                if (isStreamActive) {
                    console.log('Absen: Reusing warm camera stream from context');
                    // Camera is already active, just show UI
                    setShowCamera(true);
                    setLoading(false);
                    loadBackgroundData();
                    return;
                }
            }

            // No warm stream, start camera
            console.log('Absen: No warm stream, starting camera');
            startCamera().catch(err => {
                console.error('Failed to start camera:', err);
                setError('Gagal mengakses kamera. Pastikan izin kamera diberikan.');
            });

            // Show camera UI IMMEDIATELY - don't wait for camera to be ready
            setShowCamera(true);
            
            // Remove loading state - camera UI is now visible
            setLoading(false);

            // Load other data in background (non-blocking)
            loadBackgroundData();

        } catch (err) {
            console.error('Initialization error:', err);
            setError('Gagal memuat sistem absensi. Silakan coba lagi.');
            setLoading(false);
        }
    };

    const calculateDistance = (lat1, lon1, lat2, lon2) => {
        const R = 6371000; // Earth radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    };

    const loadBackgroundData = async () => {
        try {
            const [typeResponse, officeResponse, loc] = await Promise.all([
                axios.get(`${APP_BASE_URL}/api/attendance/determine-next-type`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }),
                axios.get(`${APP_BASE_URL}/api/attendance/office-location`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                }),
                getCurrentLocation()
            ]);

            setNextType(typeResponse.data.next_type);
            setLastType(typeResponse.data.last_type);

            // Calculate geofence
            const distance = calculateDistance(
                loc.latitude,
                loc.longitude,
                officeResponse.data.office.latitude,
                officeResponse.data.office.longitude
            );

            const isWithinGeofence = distance <= officeResponse.data.office.radius_meter;

            setLocationInfo({
                ...loc,
                distance,
                isWithinGeofence,
                office: officeResponse.data.office
            });
        } catch (err) {
            console.error('Error loading background data:', err);
            // Don't block camera if this fails
        }
    };

    const handleCapture = async () => {
        if (!videoRef.current || !cameraReady) return;

        setSubmitting(true);
        setError(null);
        setFaceMismatch(false);

        try {
            // Load face-api models on-demand (only when user clicks capture)
            await loadFaceApiModels();

            // Get reference descriptor
            const descriptorResponse = await axios.get(`${APP_BASE_URL}/api/attendance/reference-descriptor`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            const referenceDescriptor = descriptorResponse.data.descriptor;

            // Capture face with multi-frame consensus
            const captureResult = await captureFace(videoRef.current, referenceDescriptor);

            if (!captureResult.consensusMet) {
                setFaceMismatch(true);
                setSubmitting(false);
                return;
            }

            // Capture photo
            const photo = capturePhoto(videoRef.current);

            // Submit attendance
            const response = await axios.post(`${APP_BASE_URL}/api/attendance/store`, {
                latitude: locationInfo.latitude,
                longitude: locationInfo.longitude,
                face_match_score: captureResult.avgDistance,
                photo: photo,
                device_info: navigator.userAgent
            }, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            setResult(response.data);

        } catch (err) {
            console.error('Capture error:', err);
            if (err.response?.data?.error === 'face_mismatch') {
                setFaceMismatch(true);
            } else {
                setError(err.response?.data?.message || 'Gagal melakukan absensi. Silakan coba lagi.');
            }
        } finally {
            setSubmitting(false);
        }
    };

    const handleRetry = () => {
        setFaceMismatch(false);
        setError(null);
    };

    const handleBackToDashboard = () => {
        navigate('/app/riwayat');
    };

    const handleAbsenLagi = () => {
        setResult(null);
        setFaceMismatch(false);
        setError(null);
    };

    if (loading) {
        return (
            <div className="p-4">
                <div className="text-center py-8">
                    <div className="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 mb-4">
                        <svg className="w-6 h-6 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p className="text-gray-800 text-sm">Menyiapkan kamera...</p>
                </div>
            </div>
        );
    }

    if (result) {
        return (
            <div className="p-4">
                <div className="text-center py-8">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg mb-4">
                        <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 className="text-xl font-bold text-gray-800 mb-2">Absen Berhasil!</h2>
                    <p className="text-gray-600 text-sm mb-4">{result.message}</p>
                    <div className="flex gap-3">
                        <button
                            onClick={handleBackToDashboard}
                            className="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-xl transition-colors"
                        >
                            Kembali ke Dashboard
                        </button>
                        <button
                            onClick={handleAbsenLagi}
                            className="flex-1 py-3 bg-gradient-to-r from-amber-400 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-white font-medium rounded-xl transition-colors"
                        >
                            Absen Lagi
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="p-4">
            {/* Header */}
            <div className="text-center mb-6">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-300 to-amber-500 shadow-lg mb-3">
                    <svg className="w-8 h-8 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 className="text-2xl font-bold text-gray-800">Absensi</h1>
                <p className="text-gray-500 text-sm mt-1">
                    {nextType === 'IN' ? 'Absen Masuk' : 'Absen Pulang'}
                </p>
            </div>

            {/* Camera Card */}
            <div className="bg-white rounded-3xl p-5 shadow-lg">
                {/* Location Info */}
                {locationInfo && (
                    <div className={`mb-4 p-3 rounded-2xl text-sm text-center ${
                        locationInfo.isWithinGeofence
                            ? 'bg-green-50 border border-green-200 text-green-700'
                            : 'bg-amber-50 border border-amber-200 text-amber-700'
                    }`}>
                        {locationInfo.isWithinGeofence
                            ? '📍 Di dalam area kantor'
                            : locationInfo.distance 
                                ? `📍 Di luar area kantor (${Math.round(locationInfo.distance)}m)`
                                : '📍 Memuat lokasi...'
                        }
                    </div>
                )}

                {/* Camera */}
                {showCamera && (
                    <div className="flex justify-center mb-4">
                        <div className="relative inline-block" style={{ maxWidth: '360px', width: '100%' }}>
                            <video
                                ref={videoRef}
                                autoPlay
                                playsInline
                                muted
                                className="w-full rounded-2xl"
                                style={{ transform: 'scaleX(-1)', background: '#fff7d6' }}
                            />
                            
                            {/* Processing Overlay */}
                            {isProcessing && (
                                <div className="absolute inset-0 flex flex-col items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm">
                                    <div className="w-10 h-10 border-4 border-amber-200 border-t-amber-500 rounded-full animate-spin mb-2"></div>
                                    <p className="text-amber-700 text-sm font-medium">Mendeteksi wajah...</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Face Mismatch Alert */}
                {faceMismatch && (
                    <div className="mb-4 bg-red-50 border-2 border-red-200 rounded-2xl p-4 text-center">
                        <svg className="w-10 h-10 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <p className="text-red-700 text-sm font-medium">Wajah tidak dikenali</p>
                        <p className="text-red-600 text-xs mt-1">Pastikan wajah terlihat jelas dan pencahayaan cukup</p>
                        <button
                            onClick={handleRetry}
                            className="mt-3 px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm rounded-xl transition-colors"
                        >
                            Coba Lagi
                        </button>
                    </div>
                )}

                {/* Error Message */}
                {error && !faceMismatch && (
                    <div className="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl p-3 text-center">
                        {error}
                    </div>
                )}

                {/* Capture Button */}
                <button
                    onClick={handleCapture}
                    disabled={!cameraReady || submitting || isProcessing}
                    className="w-full py-4 bg-gradient-to-r from-amber-400 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-white font-semibold text-lg rounded-2xl shadow-lg transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {submitting || isProcessing ? 'Memproses...' : 'Ambil Absen'}
                </button>
            </div>
        </div>
    );
}

export default Absen;
