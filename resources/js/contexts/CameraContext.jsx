import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';

const CameraContext = createContext(null);

export function CameraProvider({ children }) {
    const [stream, setStream] = useState(null);
    const [isReady, setIsReady] = useState(false);
    const [error, setError] = useState(null);
    const [isWarmedUp, setIsWarmedUp] = useState(false);
    const [isInitialized, setIsInitialized] = useState(false);
    const [cameraPageCount, setCameraPageCount] = useState(0);

    const startCamera = useCallback(async () => {
        // If already has a live stream, return it immediately
        if (stream) {
            // Check if stream tracks are still active
            const tracks = stream.getTracks();
            const isStreamActive = tracks.some(track => track.readyState === 'live');
            
            if (isStreamActive) {
                console.log('CameraContext: Reusing existing warm stream');
                return stream;
            } else {
                // Stream exists but tracks are dead, clean it up
                console.log('CameraContext: Existing stream has dead tracks, cleaning up');
                tracks.forEach(track => track.stop());
                setStream(null);
                setIsReady(false);
            }
        }

        // Start fresh camera
        try {
            const constraints = {
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    frameRate: { ideal: 30 }
                },
                audio: false
            };

            const mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
            
            setStream(mediaStream);
            setError(null);
            setIsInitialized(true);
            
            console.log('CameraContext: Started new camera stream');
            return mediaStream;
        } catch (err) {
            console.error('CameraContext: Error starting camera:', err);
            setError(err.message);
            setIsInitialized(false);
            throw err;
        }
    }, [stream]);

    const stopCamera = useCallback(() => {
        if (stream) {
            console.log('CameraContext: Stopping camera stream');
            stream.getTracks().forEach(track => track.stop());
            setStream(null);
            setIsReady(false);
            setIsWarmedUp(false);
            setIsInitialized(false);
        }
    }, [stream]);

    const setIsReadyCallback = useCallback((ready) => {
        setIsReady(ready);
    }, []);

    // Track when camera pages are mounted
    const registerCameraPage = useCallback(() => {
        setCameraPageCount(prev => prev + 1);
    }, []);

    const unregisterCameraPage = useCallback(() => {
        setCameraPageCount(prev => {
            const newCount = prev - 1;
            if (newCount <= 0) {
                // No camera pages are active, stop the camera
                console.log('CameraContext: No camera pages active, stopping camera');
                stopCamera();
            }
            return Math.max(0, newCount);
        });
    }, [stopCamera]);

    // Cleanup on unmount of provider
    useEffect(() => {
        return () => {
            if (stream) {
                console.log('CameraContext: Cleanup - stopping camera on unmount');
                stream.getTracks().forEach(track => track.stop());
            }
        };
    }, [stream]);

    const value = {
        stream,
        isReady,
        error,
        isWarmedUp,
        isInitialized,
        cameraPageCount,
        startCamera,
        stopCamera,
        setIsReady: setIsReadyCallback,
        registerCameraPage,
        unregisterCameraPage
    };

    return (
        <CameraContext.Provider value={value}>
            {children}
        </CameraContext.Provider>
    );
}

export function useCameraContext() {
    const context = useContext(CameraContext);
    if (!context) {
        throw new Error('useCameraContext must be used within CameraProvider');
    }
    return context;
}
