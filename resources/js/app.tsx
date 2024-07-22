import './bootstrap';
import '../css/app.css';

import ReactDOM from 'react-dom/client';
import React from 'react';
import Login from './Page/Login';
import Home from './Page/Home';

ReactDOM.createRoot(document.getElementById('app')).render(     
    <Login />
    // <Home />
);