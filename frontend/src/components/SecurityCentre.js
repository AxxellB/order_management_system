import React, { useState } from 'react';
import axios from 'axios';
import ProfileNavbar from './ProfileNavBar';
import styles from '../styles/SecurityCentre.module.css';

const SecurityCentre = () => {
    const [formData, setFormData] = useState({
        oldPassword: '',
        newPassword: '',
        confirmPassword: ''
    });

    const [errorMessage, setErrorMessage] = useState('');
    const [successMessage, setSuccessMessage] = useState('');

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            [name]: value,
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.put('/api/change-password', formData);
            setSuccessMessage(response.data.message);
            setErrorMessage('');
        } catch (error) {
            if (error.response) {
                setErrorMessage(error.response.data.message);
                setSuccessMessage('');
            }
        }
    };

    return (
        <div className={styles.securityCenterContainer}>
            <ProfileNavbar/>

            {errorMessage && <div className={styles.errorMessage}>{errorMessage}</div>}
            {successMessage && <div className={styles.successMessage}>{successMessage}</div>}

            <form onSubmit={handleSubmit}>
                <div className={styles.securityCenterFormGroup}>
                    <label>Old Password</label>
                    <input
                        type="password"
                        name="oldPassword"
                        value={formData.oldPassword}
                        onChange={handleChange}
                        required
                    />
                </div>
                <div className={styles.securityCenterFormGroup}>
                    <label>New Password</label>
                    <input
                        type="password"
                        name="newPassword"
                        value={formData.newPassword}
                        onChange={handleChange}
                        required
                    />
                </div>
                <div className={styles.securityCenterFormGroup}>
                    <label>Confirm New Password</label>
                    <input
                        type="password"
                        name="confirmPassword"
                        value={formData.confirmPassword}
                        onChange={handleChange}
                        required
                    />
                </div>
                <button type="submit" className={`btn btn-primary ${styles.submitButton}`}>Submit</button>
            </form>
        </div>
    );
};

export default SecurityCentre;
