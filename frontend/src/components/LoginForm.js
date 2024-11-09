import React, { useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";
import { useAlert } from "../provider/AlertProvider";

const LoginForm = () => {
    const [formData, setFormData] = useState({
        email: "",
        password: "",
    });
    const [errors, setErrors] = useState({});
    const [requires2FA, setRequires2FA] = useState(false);
    const [userId, setUserId] = useState(null);
    const [twoFactorCode, setTwoFactorCode] = useState("");

    const { setToken } = useAuth();
    const { showAlert } = useAlert();
    const navigate = useNavigate();

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
        setErrors((prevErrors) => ({ ...prevErrors, [name]: "" }));
    };

    const handle2FAChange = (e) => {
        setTwoFactorCode(e.target.value);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        let formErrors = {};
        const { email, password } = formData;

        if (!email) formErrors.email = "Email cannot be empty";
        if (!password) formErrors.password = "Password cannot be empty";
        setErrors(formErrors);

        if (Object.keys(formErrors).length > 0) {
            return;
        }

        try {
            const response = await axios.post('/api/login', { email, password });
            if (response.status === 200) {
                if (response.data.requires2FA) {
                    setRequires2FA(true);
                    setUserId(response.data.userId);
                } else {
                    setToken(response.data.token);
                    showAlert("Login successful!", "success");
                    navigate('/');
                }
            }
        } catch (error) {
            if (error.response) {
                if (error.response.status === 401) {
                    showAlert("Wrong email or password", "error");
                } else {
                    showAlert(error.response.data.message || "An error occurred. Please try again.", "error");
                }
            } else {
                showAlert("A network error occurred. Please check your connection.", "error");
            }
        }
    };

    const handle2FASubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post('/api/verify-2fa', {
                user_id: userId,
                code: twoFactorCode,
            });
            if (response.status === 200) {
                setToken(response.data.token);
                showAlert("2FA verification successful!", "success");
                navigate('/');
            }
        } catch (error) {
            if (error.response) {
                showAlert(error.response.data.message || "Invalid 2FA code. Please try again.", "error");
            } else {
                showAlert("Network error: " + error.message, "error");
            }
        }
    };

    const handleResend2FA = async () => {
        try {
            const response = await axios.post('/api/resend-2fa', { user_id: userId });
            if (response.status === 200) {
                showAlert(response.data.message, "success");
            }
        } catch (error) {
            if (error.response) {
                showAlert(error.response.data.message || "Failed to resend 2FA code.", "error");
            } else {
                showAlert("A network error occurred. Please check your connection.", "error");
            }
        }
    };

    return (
        <div className="container mt-1">
            {!requires2FA ? (
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
            ) : (
                <form onSubmit={handle2FASubmit} className="border p-4 rounded shadow">
                    <div className="mb-3">
                        <label htmlFor="2faCode" className="form-label">Enter 2FA Code</label>
                        <input
                            type="text"
                            name="2faCode"
                            className="form-control"
                            value={twoFactorCode}
                            onChange={handle2FAChange}
                        />
                    </div>
                    <button type="submit" className="btn btn-primary btn-sm me-2">Verify Code</button>
                    <button type="button" className="btn btn- btn-warning btn-sm" onClick={handleResend2FA}>
                        Resend Code
                    </button>
                </form>
            )}
        </div>
    );
};

export default LoginForm;
