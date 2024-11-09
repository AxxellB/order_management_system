import React, {useState} from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";
import {useAlert} from "../provider/AlertProvider";
import {forEach} from "react-bootstrap/ElementChildren";

const RegisterForm = () => {
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        password: "",
        confirmPassword: ""
    });

    const navigate = useNavigate();
    const [errors, setErrors] = useState({});
    const {showAlert} = useAlert();

    const handleChange = (e) => {
        const {name, value} = e.target;
        setFormData({...formData, [name]: value});
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        let formErrors = {};
        let firstName = formData.firstName;
        let lastName = formData.lastName;
        let email = formData.email;
        let password = formData.password
        let confirmPassword = formData.confirmPassword

        if (!firstName) formErrors.firstName = "First name is required";
        if (!lastName) formErrors.lastName = "Last name is required";
        if (!email) formErrors.email = "Email is required";
        if (!password) formErrors.password = "Password is required";
        if (!confirmPassword) formErrors.confirmPassword = "Confirm Password is required";

        setErrors(formErrors);

        if (Object.keys(formErrors).length === 0) {
            axios.post('http://localhost/api/register', {
                firstName,
                lastName,
                email,
                password,
                confirmPassword
            })
                .then(function (response) {
                    if (response.status === 201) {
                        navigate('/login');
                    }
                })
                .catch(function (error) {
                    if (error.response && error.response.data.errors) {
                        const apiErrors = error.response.data.errors;
                        const formErrors = {};

                        Object.entries(apiErrors).forEach(([field, messages]) => {
                            formErrors[field] = messages.join(' ');
                        });

                        setErrors(formErrors);
                    } else {
                        showAlert("An error occurred. Please try again.");
                    }
                });
        }
    };

    return (
        <div className="container mt-3">
            <form onSubmit={handleSubmit} className="border p-4 rounded shadow">
                <div className="mb-3">
                    <label htmlFor="firstName" className="form-label">First Name</label>
                    <input
                        type="text"
                        name="firstName"
                        className={`form-control ${errors.firstName ? 'is-invalid' : ''}`}
                        value={formData.firstName}
                        onChange={handleChange}
                    />
                    {errors.firstName && <div className="invalid-feedback">{errors.firstName}</div>}
                </div>
                <div className="mb-3">
                    <label htmlFor="lastName" className="form-label">Last Name</label>
                    <input
                        type="text"
                        name="lastName"
                        className={`form-control ${errors.lastName ? 'is-invalid' : ''}`}
                        value={formData.lastName}
                        onChange={handleChange}
                    />
                    {errors.lastName && <div className="invalid-feedback">{errors.lastName}</div>}
                </div>
                <div className="mb-3">
                    <label htmlFor="email" className="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                        value={formData.email}
                        onChange={handleChange}
                    />
                    {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                </div>
                <div className="mb-3">
                    <label htmlFor="password" className="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                        value={formData.password}
                        onChange={handleChange}
                    />
                    {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                </div>
                <div className="mb-3">
                    <label htmlFor="confirmPassword" className="form-label">Confirm Password</label>
                    <input
                        type="password"
                        name="confirmPassword"
                        className={`form-control ${errors.confirmPassword ? 'is-invalid' : ''}`}
                        value={formData.confirmPassword}
                        onChange={handleChange}
                    />
                    {errors.confirmPassword && <div className="invalid-feedback">{errors.confirmPassword}</div>}
                </div>
                <button type="submit" className="btn btn-primary">Register</button>
            </form>
        </div>
    );
};

export default RegisterForm;
