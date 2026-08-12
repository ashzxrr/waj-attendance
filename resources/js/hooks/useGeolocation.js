import { useState, useEffect, useCallback } from 'react';

export function useGeolocation() {
    const [location, setLocation] = useState(null);
    const [error, setError] = useState(null);
    const [isLoading, setIsLoading] = useState(false);

    const getCurrentLocation = useCallback(() => {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                const err = new Error('Geolocation not supported');
                setError(err.message);
                reject(err);
                return;
            }

            setIsLoading(true);
            setError(null);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const loc = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    setLocation(loc);
                    setIsLoading(false);
                    resolve(loc);
                },
                (err) => {
                    setError(err.message);
                    setIsLoading(false);
                    reject(err);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    }, []);

    return {
        location,
        error,
        isLoading,
        getCurrentLocation
    };
}
