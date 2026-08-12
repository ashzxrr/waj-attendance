import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import PageShell from './layout/PageShell';
import Riwayat from './pages/Riwayat';
import Absen from './pages/Absen';
import FaceRegistration from './pages/FaceRegistration';

function App() {
    return (
        <>
            <Routes>
                <Route path="/app" element={<PageShell />}>
                    <Route index element={<Navigate to="/app/riwayat" replace />} />
                    <Route path="riwayat" element={<Riwayat />} />
                    <Route path="absen" element={<Absen />} />
                    <Route path="face-registration" element={<FaceRegistration />} />
                    {/* Future routes will be added here */}
                    {/* <Route path="profile" element={<Profile />} /> */}
                </Route>
                <Route path="/" element={<Navigate to="/app/riwayat" replace />} />
            </Routes>
        </>
    );
}

export default App;
