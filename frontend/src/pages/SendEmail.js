import React from "react";
import axios from "axios";

const SendEmail = () => {
    const handleSendEmail = async () => {
        try {
            const response = await axios.post("http://localhost/api/send-email");
            if (response.status === 200) {
                alert("Email sent successfully");
            } else {
                alert("Failed to send email");
            }
        } catch (error) {
            alert("An error occurred while sending email");
        }
    };

    return (
        <div className="container mt-5">
            <h1>Send Email</h1>
            <button className="btn btn-primary" onClick={handleSendEmail}>
                Send Test Email
            </button>
        </div>
    );
};

export default SendEmail;
