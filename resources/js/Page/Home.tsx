// Import our custom CSS
import React from 'react';
import '../../scss/app.scss'

// Import all of Bootstrap's JS
import * as bootstrap from 'bootstrap'

export default function Home() {
    const heading = "Laravel 9 Vite  with React JS";
    // return <div> {heading}</div>;
    return <button type="submit" className="btn btn-primary">Submit</button>;
}