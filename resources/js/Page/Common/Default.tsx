import React from "react";
import '../../../scss/app.scss';

const Default = ({ children }) => {
    return (
        <>
        <html lang="en">
        <body data-bs-theme="dark">
            <main>
                { children }
            </main>
        </body>
        </html>
        </>
    );
}

export default Default;