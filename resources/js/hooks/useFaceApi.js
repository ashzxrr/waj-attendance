import { useState, useEffect, useRef } from 'react';

const DEBUG_ALERTS = false;

// Global flag to prevent multiple model loads
let modelsLoaded = false;
let loadPromise = null;

export async function preloadFaceApiModels() {
    if (modelsLoaded) {
        return true;
    }

    if (loadPromise) {
        return loadPromise;
    }

    const APP_BASE_URL = document.querySelector('meta[name="app-base-url"]')?.content?.replace(/\/$/, '') || '';
    const MODEL_URL = `${APP_BASE_URL}/vendor/face-api/models`;

    loadPromise = (async () => {
        try {
            // Load face-api models
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            modelsLoaded = true;
            return true;
        } catch (err) {
            console.error('Error loading face-api models:', err);
            loadPromise = null; // Allow retry
            throw err;
        }
    })();

    return loadPromise;
}

export function useFaceApi() {
    const [isLoaded, setIsLoaded] = useState(modelsLoaded);
    const [error, setError] = useState(null);

    useEffect(() => {
        setIsLoaded(modelsLoaded);
    }, []);

    const loadModels = async () => {
        if (modelsLoaded) {
            return true;
        }

        if (loadPromise) {
            const result = await loadPromise;
            setIsLoaded(modelsLoaded);
            return result;
        }

        const result = await preloadFaceApiModels();
        setIsLoaded(modelsLoaded);
        return result;
    };

    return { isLoaded, error, loadModels };
}
