import { useEffect, useRef, useState } from 'react';
import { useCameraContext } from '../contexts/CameraContext.jsx';

const DEBUG_ALERTS = false;

export function useCamera() {
    const { stream, isReady, error, startCamera, stopCamera, setIsReady } = useCameraContext();
    const videoRef = useRef(null);
    const [localReady, setLocalReady] = useState(isReady);

    useEffect(() => {
        setLocalReady(isReady);
    }, [isReady]);

    useEffect(() => {
        if (!videoRef.current) return;

        if (stream) {
            videoRef.current.srcObject = stream;

            if (videoRef.current.readyState >= 1) {
                setLocalReady(true);
                setIsReady(true);
            } else {
                const handleLoadedMetadata = () => {
                    setLocalReady(true);
                    setIsReady(true);
                };

                videoRef.current.addEventListener('loadedmetadata', handleLoadedMetadata, { once: true });
            }
        } else {
            videoRef.current.srcObject = null;
            setLocalReady(false);
            setIsReady(false);
        }
    }, [stream, setIsReady]);

    return {
        stream,
        isReady: localReady,
        error,
        videoRef,
        startCamera,
        stopCamera
    };
}
