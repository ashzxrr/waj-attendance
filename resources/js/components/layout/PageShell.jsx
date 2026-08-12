import React, { useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import BottomNav from './BottomNav';
import { useCameraContext } from '../../contexts/CameraContext.jsx';

function PageShell() {
    const location = useLocation();
    const isLightPage = location.pathname === '/app/absen' || location.pathname === '/app/face-registration';
    const hideNav = location.pathname === '/app/face-registration';
    
    // Use shared camera context for warm-up
    const { startCamera: warmUpCamera, stream, isWarmedUp, isInitialized } = useCameraContext();

    useEffect(() => {
        // Warm up camera when on riwayat page (anticipate user will go to absen)
        if (location.pathname === '/app/riwayat' && !isWarmedUp && !isInitialized) {
            console.log('PageShell: Warming up camera on riwayat page');
            warmUpCamera().then(() => {
                console.log('PageShell: Camera warm-up successful');
            }).catch(err => {
                console.log('PageShell: Camera warm-up failed (expected):', err.message);
            });
        }
    }, [location.pathname, warmUpCamera, isWarmedUp, isInitialized]);

    // Stop camera when user leaves both riwayat and absen (e.g., logout, or navigating to other pages)
    useEffect(() => {
        const cameraPages = ['/app/riwayat', '/app/absen'];
        const isOnCameraPage = cameraPages.includes(location.pathname);
        
        if (!isOnCameraPage && stream) {
            console.log('PageShell: User left camera pages, stopping camera');
            // The context cleanup will handle stopping the stream
        }
    }, [location.pathname, stream]);

    return (
        <div className={`min-h-screen ${
            isLightPage
                ? 'bg-gradient-to-br from-amber-50 to-white'
                : 'bg-gradient-to-br from-slate-900 to-slate-800'
        }`}>
            <div className={`max-w-lg ${hideNav ? '' : 'pb-28'}`}>
                <Outlet />
            </div>
            {!hideNav && <BottomNav />}
        </div>
    );
}

export default PageShell;
