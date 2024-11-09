import React, { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";
import styles from "../styles/Logout.module.css";

const Logout = () => {
    const { setToken } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        const timer = setTimeout(() => {
            setToken();
            navigate("/", { replace: true });
        }, 3000);

        return () => clearTimeout(timer);
    }, [setToken, navigate]);

    return (
        <div className={styles.overlay}>
            <div className={styles.box}>
                <p className={styles.message}>You are being logged out</p>
                <div className={`spinner-border ${styles.spinner}`} role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
                <p className={styles.message}>Redirecting to the homepage in a few moments...</p>
            </div>
        </div>
    );
};

export default Logout;
