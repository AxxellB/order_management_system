import React, {useState} from "react";
import axios from "axios";
import {useNavigate} from "react-router-dom";
import {useAuth} from "../provider/AuthProvider";
import {useAlert} from "../provider/AlertProvider";

const LoginForm = () => {
    const [formData, setFormData] = useState({
        email: "",
        password: "",
    });

    const [errors, setErrors] = useState({});
    const {setToken} = useAuth();
    const {showAlert} = useAlert();
    const navigate = useNavigate();

    const handleChange = (e) => {
        const {name, value} = e.target;
        setFormData({...formData, [name]: value});
        setErrors((prevErrors) => ({...prevErrors, [name]: ""}));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        let formErrors = {};
        const {email, password} = formData;
        
        if (!email) formErrors.email = "Email cannot be empty";
        if (!password) formErrors.password = "Password cannot be empty";

        setErrors(formErrors);

        if (Object.keys(formErrors).length === 0) {
            try {
                const response = await axios.post('/api/login', {email, password});
                if (response.status === 200) {
                    setToken(response.data.token);
                    navigate('/');
                }
            } catch (error) {
                if (error.response) {
                    if (error.response.status === 401) {
                        setErrors({
                            email: "",
                            password: "Wrong email or password"
                        });
                    } else {
                        showAlert("An error occurred. Please try again.", "error");
                    }
                } else {
                    showAlert("A network error occurred. Please check your connection.", "error");
                }
            }
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
                        className={`form-control ${errors.email || errors.password ? 'is-invalid' : ''}`}
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
