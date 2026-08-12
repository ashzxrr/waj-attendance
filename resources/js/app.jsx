import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './components/App.jsx';
import { CameraProvider } from './contexts/CameraContext.jsx';
import '../css/app.css';

console.error('BEFORE RENDER - Checking if app.jsx executes');
console.error('Target element:', document.getElementById('app'));

const targetElement = document.getElementById('app');
if (!targetElement) {
    console.error('ERROR: Target element #app not found!');
    console.error('Available elements with IDs:', Array.from(document.querySelectorAll('[id]')).map(el => el.id));
} else {
    console.error('Target element found:', targetElement);
}

ReactDOM.createRoot(targetElement).render(
    <React.StrictMode>
        <BrowserRouter>
            <CameraProvider>
                <App />
            </CameraProvider>
        </BrowserRouter>
    </React.StrictMode>
);

console.error('AFTER RENDER - Render call completed');
