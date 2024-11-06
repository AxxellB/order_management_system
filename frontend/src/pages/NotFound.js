import React from "react";
import { useNavigate } from "react-router-dom";

const NotFound = () => {
    const navigate = useNavigate();

    return (
        <div style={styles.container}>
            <h1 style={styles.title}>404</h1>
            <p style={styles.message}>Page Not Found</p>
            <p style={styles.description}>
                The page you’re looking for doesn’t exist or has been moved.
            </p>
            <button style={styles.button} onClick={() => navigate("/")}>
                Go Back to Homepage
            </button>
        </div>
    );
};

const styles = {
    container: {
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        height: "100vh",
        textAlign: "center",
        color: "#333",
        backgroundColor: "#f8f8f8",
    },
    title: {
        fontSize: "6rem",
        fontWeight: "bold",
        color: "#ff6b6b",
    },
    message: {
        fontSize: "2rem",
        fontWeight: "600",
    },
    description: {
        fontSize: "1.2rem",
        marginTop: "1rem",
        maxWidth: "400px",
        color: "#555",
    },
    button: {
        marginTop: "2rem",
        padding: "0.75rem 1.5rem",
        fontSize: "1rem",
        color: "#fff",
        backgroundColor: "#007bff",
        border: "none",
        borderRadius: "5px",
        cursor: "pointer",
    },
};

export default NotFound;
