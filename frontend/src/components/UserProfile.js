import React, {useEffect, useState} from 'react';
import axios from 'axios';
import "../styles/ProfileTab.css";
import ProfileNavbar from './ProfileNavBar';

const ProfileTab = () => {
    const [user, setUser] = useState(null);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchUserProfile = async () => {
            setLoading(true);
            try {
                const response = await axios.get('/api/user-profile');
                setUser(response.data);
            } catch (error) {
                setError('Failed to load profile data.');
            } finally {
                setLoading(false);
            }
        };

        fetchUserProfile();
    }, []);

    if (loading) {
        return (
            <div className="d-flex justify-content-center">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (error) {
        return <div>{error}</div>;
    }

    return (
        <div className="profile-container">
            <ProfileNavbar/>

            <div className="profile-info">
                <h2>Profile Information</h2>
                <p><strong>First Name:</strong> {user.firstName}</p>
                <p><strong>Last Name:</strong> {user.lastName}</p>
                <p><strong>Email:</strong> {user.email}</p>
            </div>
        </div>
    );
};

export default ProfileTab;
