import { useState, useCallback } from 'react';

export function useFaceCapture() {
    const [isProcessing, setIsProcessing] = useState(false);
    const [captureResults, setCaptureResults] = useState([]);
    const [faceMatchScore, setFaceMatchScore] = useState(null);

    const captureFace = useCallback(async (videoElement, referenceDescriptor) => {
        setIsProcessing(true);
        setCaptureResults([]);

        const results = [];
        const CAPTURE_COUNT = 3;
        const CAPTURE_INTERVAL = 500; // 500ms between captures
        const THRESHOLD = 0.45; // Euclidean distance threshold

        try {
            for (let i = 0; i < CAPTURE_COUNT; i++) {
                // Detect face
                const detection = await faceapi
                    .detectSingleFace(videoElement, new faceapi.SsdMobilenetv1Options())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    results.push({ success: false, reason: 'no_face' });
                    continue;
                }

                // Calculate distance from reference
                const distance = faceapi.euclideanDistance(
                    detection.descriptor,
                    referenceDescriptor
                );

                const isMatch = distance <= THRESHOLD;
                results.push({
                    success: true,
                    distance,
                    isMatch,
                    detection
                });

                // Wait before next capture (except for the last one)
                if (i < CAPTURE_COUNT - 1) {
                    await new Promise(resolve => setTimeout(resolve, CAPTURE_INTERVAL));
                }
            }

            setCaptureResults(results);

            // Multi-frame consensus: at least 2/3 must match
            const successfulMatches = results.filter(r => r.success && r.isMatch);
            const consensusMet = successfulMatches.length >= 2;

            // Calculate average distance from successful matches
            const avgDistance = successfulMatches.length > 0
                ? successfulMatches.reduce((sum, r) => sum + r.distance, 0) / successfulMatches.length
                : null;

            setFaceMatchScore(avgDistance);

            return {
                consensusMet,
                avgDistance,
                results,
                successfulMatches: successfulMatches.length
            };

        } catch (err) {
            console.error('Error capturing face:', err);
            return {
                consensusMet: false,
                error: err.message,
                results
            };
        } finally {
            setIsProcessing(false);
        }
    }, []);

    const capturePhoto = useCallback((videoElement) => {
        // Create canvas for photo capture
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        // Set canvas size to match video
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;

        // Draw video frame to canvas
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

        // Resize to 640px width for upload
        const resizedCanvas = document.createElement('canvas');
        const resizedContext = resizedCanvas.getContext('2d');
        const targetWidth = 640;
        const scaleFactor = targetWidth / canvas.width;
        const targetHeight = canvas.height * scaleFactor;

        resizedCanvas.width = targetWidth;
        resizedCanvas.height = targetHeight;

        resizedContext.drawImage(canvas, 0, 0, targetWidth, targetHeight);

        // Convert to base64
        return resizedCanvas.toDataURL('image/jpeg', 0.85);
    }, []);

    return {
        isProcessing,
        captureResults,
        faceMatchScore,
        captureFace,
        capturePhoto
    };
}
