import React from 'react';
import { Link } from 'react-router-dom';
import '../styles/ProfileNavBar.css';

const ProfileNavbar = () => {
    return (
        <nav className="profile-navbar">
            <Link to="/profile"><button className="profile-button">Profile</button></Link>
            <Link to="/profile/security-centre"><button className="profile-button">Security Centre</button></Link>
            <Link to="/profile/addresses"><button className="profile-button">Addresses</button></Link>
            <Link to="/profile/orders"><button className="profile-button">My Orders</button></Link>
        </nav>
    );
};

export default ProfileNavbar;
