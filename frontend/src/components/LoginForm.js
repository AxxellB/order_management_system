import React, { useState } from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";

const LoginForm = () => {
    const [formData, setFormData] = useState({
        email: "",
        password: "",
    });

    const [errors, setErrors] = useState({});
    const navigate = useNavigate();
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        let formErrors = {};
        let email = formData.email;
        let password = formData.password
        if (!email) formErrors.email = "Email is can not be empty";
        if (!password) formErrors.password = "Password can not be empty";

        setErrors(formErrors);

        axios.post('/api/login',{
                email,
                password,
            }
        ).then(function (response) {
            if(response.status === 200){
                localStorage.setItem('jwtToken', response.data.token);
                navigate('/homepage');
            } else {
                alert(response.data.message);
            }
        }).catch(function (error) {
            if (error.response) {
                alert(error.response.data.message || "An error occurred. Please try again.");
            } else {
                alert("Network error: " + error.message);
            }
        });

        if (Object.keys(formErrors).length === 0) {
            console.log("Form data submitted: ", formData);
        }
    };

    return (
        <div className="container mt-1">
            <form onSubmit={handleSubmit} className="border p-4 rounded shadow">
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
                <button type="submit" className="btn btn-primary">Login</button>
            </form>
        </div>
    );
};

export default LoginForm;
