import React from 'react';
import LoginForm from '../components/LoginForm';

const Login = () => {
    return (
        <div className="container d-flex flex-column justify-content-center align-items-center mt-5">
            <h1 className="text-center mb-4">Login</h1>
            <LoginForm />
        </div>
    );
};

export default Login;