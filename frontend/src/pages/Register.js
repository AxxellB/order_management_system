import React from 'react';
import RegisterForm from '../components/RegisterForm';

const Register = () => {
    return (
        <div className="container d-flex flex-column justify-content-center align-items-center mt-5">
            <h1>Register</h1>
            <RegisterForm />
        </div>
    );
};

export default Register;