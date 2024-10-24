import React from 'react';
import './App.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min';
import 'bootstrap-icons/font/bootstrap-icons.css';
import AuthProvider from "./provider/AuthProvider";
import Routes from "./routes";


function App() {
    return (
        <AuthProvider>
            <Routes />
        </AuthProvider>
    );

}

export default App;