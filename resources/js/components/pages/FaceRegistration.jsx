import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useCameraContext } from '../../contexts/CameraContext.jsx';
import { useCamera } from '../../hooks/useCamera';
import { useFaceApi } from '../../hooks/useFaceApi';
import { useFaceCapture } from '../../hooks/useFaceCapture';

function FaceRegistration() {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);
    const [capturedPhotos, setCapturedPhotos] = useState([]);
    const [isCapturing, setIsCapturing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(false);

    // Custom hooks
    const { isLoaded: faceApiLoaded } = useFaceApi();
    const { stream, isReady: cameraReady, videoRef, startCamera, stopCamera } = useCamera();
    const { registerCameraPage, unregisterCameraPage } = useCameraContext();
    const { capturePhoto } = useFaceCapture();

    const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]')?.content?.replace(/\/$/, '') || '';
    const token = localStorage.getItem('token');

    const REQUIRED_PHOTOS = 3;

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        // Register this as a camera page
        registerCameraPage();

        // Start initialization
        initializeApp();

        // Unregister when unmounting
        return () => {
            unregisterCameraPage();
        };
    }, [registerCameraPage, unregisterCameraPage]);

    useEffect(() => {
        // Start camera when face-api is loaded and we're ready
        if (faceApiLoaded && !loading) {
            // Check if camera is already warmed up
            if (stream) {
                const tracks = stream.getTracks();
                const isStreamActive = tracks.some(track => track.readyState === 'live');
                
                if (isStreamActive) {
                    console.log('FaceRegistration: Reusing warm camera stream');
                    return;
                }
            }
            
            // No warm stream, start camera
            startCamera().catch(err => {
                console.error('Failed to start camera:', err);
                setError('Gagal mengakses kamera. Pastikan izin kamera diberikan.');
            });
        }
    }, [faceApiLoaded, loading, startCamera, stream]);

    const initializeApp = async () => {
        try {
            setLoading(true);

            // Check if already registered
            const profileResponse = await axios.get(`${APP_BASE_URL}/api/me`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (profileResponse.data.face_registered) {
                // Already registered, redirect to dashboard
                navigate('/app/riwayat');
                return;
            }

            setLoading(false);

        } catch (err) {
            console.error('Initialization error:', err);
            setError('Gagal memuat sistem registrasi. Silakan coba lagi.');
            setLoading(false);
        }
    };

    const handleCapture = async () => {
        if (!videoRef.current || !cameraReady || capturedPhotos.length >= REQUIRED_PHOTOS) return;

        setIsCapturing(true);

        try {
            // Capture photo
            const photo = capturePhoto(videoRef.current);

            // Add to captured photos
            setCapturedPhotos([...capturedPhotos, photo]);

        } catch (err) {
            console.error('Capture error:', err);
            setError('Gagal mengambil foto. Silakan coba lagi.');
        } finally {
            setIsCapturing(false);
        }
    };

    const handleRetake = (index) => {
        const newPhotos = [...capturedPhotos];
        newPhotos.splice(index, 1);
        setCapturedPhotos(newPhotos);
    };

    const handleSubmit = async () => {
        if (capturedPhotos.length < REQUIRED_PHOTOS) {
            setError(`Harap ambil ${REQUIRED_PHOTOS} foto wajah.`);
            return;
        }

        setSubmitting(true);
        setError(null);

        try {
            // Submit photos for registration
            const response = await axios.post(`${APP_BASE_URL}/api/face-registration/store`, {
                photos: capturedPhotos
            }, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            setSuccess(true);

        } catch (err) {
            console.error('Registration error:', err);
            setError(err.response?.data?.message || 'Gagal mendaftarkan wajah. Silakan coba lagi.');
        } finally {
            setSubmitting(false);
        }
    };

    const handleContinue = () => {
        navigate('/app/riwayat');
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
                    <p className="text-gray-800 text-sm">Memuat sistem pengenalan wajah...</p>
                    <p className="text-gray-500 text-xs mt-1">Mengunduh model deteksi</p>
                </div>
            </div>
        );
    }

    if (success) {
        return (
            <div className="p-4">
                <div className="text-center py-8">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg mb-4">
                        <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 className="text-xl font-bold text-gray-800 mb-2">Registrasi Berhasil!</h2>
                    <p className="text-gray-600 text-sm mb-4">Wajah Anda telah terdaftar untuk absensi biometric.</p>
                    <button
                        onClick={handleContinue}
                        className="w-full py-3 bg-gradient-to-r from-amber-400 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-white font-medium rounded-xl transition-colors"
                    >
                        Lanjutkan
                    </button>
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
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 className="text-2xl font-bold text-gray-800">Registrasi Wajah</h1>
                <p className="text-gray-500 text-sm mt-1">Daftarkan wajah Anda untuk absensi</p>
            </div>

            {/* Camera Card */}
            <div className="bg-white rounded-3xl p-5 shadow-lg">
                {/* Instruction */}
                <div className="mb-4 bg-amber-50 border border-amber-200 rounded-2xl p-3 text-sm text-amber-700 text-center">
                    <p>Ambil {REQUIRED_PHOTOS} foto wajah dari berbagai sudut</p>
                    <p className="text-xs mt-1">Pastikan wajah terlihat jelas dan pencahayaan cukup</p>
                </div>

                {/* Camera */}
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
                    </div>
                </div>

                {/* Capture Progress */}
                <div className="flex items-center justify-center gap-3 mb-4">
                    <div className="flex gap-2">
                        {[...Array(REQUIRED_PHOTOS)].map((_, i) => (
                            <div
                                key={i}
                                className={`w-3 h-3 rounded-full transition-colors ${
                                    i < capturedPhotos.length
                                        ? 'bg-emerald-500'
                                        : 'bg-gray-300'
                                }`}
                            />
                        ))}
                    </div>
                    <span className="text-gray-600 text-sm">
                        {capturedPhotos.length} / {REQUIRED_PHOTOS}
                    </span>
                </div>

                {/* Thumbnails */}
                {capturedPhotos.length > 0 && (
                    <div className="flex justify-center gap-2 mb-4 min-h-[72px]">
                        {capturedPhotos.map((photo, index) => (
                            <div key={index} className="relative">
                                <img
                                    src={photo}
                                    alt={`Captured ${index + 1}`}
                                    className="w-16 h-16 rounded-xl object-cover"
                                    style={{ transform: 'scaleX(-1)' }}
                                />
                                <button
                                    onClick={() => handleRetake(index)}
                                    className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-600 transition-colors"
                                >
                                    ×
                                </button>
                            </div>
                        ))}
                    </div>
                )}

                {/* Error Message */}
                {error && (
                    <div className="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl p-3 text-center">
                        {error}
                    </div>
                )}

                {/* Capture Button */}
                <button
                    onClick={handleCapture}
                    disabled={!cameraReady || isCapturing || capturedPhotos.length >= REQUIRED_PHOTOS}
                    className="w-full py-4 bg-gradient-to-r from-amber-400 to-amber-600 hover:from-amber-300 hover:to-amber-500 text-white font-semibold text-lg rounded-2xl shadow-lg transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed mb-3"
                >
                    {isCapturing ? 'Memproses...' : capturedPhotos.length >= REQUIRED_PHOTOS ? 'Selesai' : 'Ambil Foto'}
                </button>

                {/* Submit Button */}
                {capturedPhotos.length >= REQUIRED_PHOTOS && (
                    <button
                        onClick={handleSubmit}
                        disabled={submitting}
                        className="w-full py-4 bg-gradient-to-r from-emerald-400 to-emerald-600 hover:from-emerald-300 hover:to-emerald-500 text-white font-semibold text-lg rounded-2xl shadow-lg transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {submitting ? 'Mendaftarkan...' : 'Daftarkan Wajah'}
                    </button>
                )}
            </div>
        </div>
    );
}

export default FaceRegistration;
