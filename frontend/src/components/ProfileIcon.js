import React, {useEffect, useState} from "react";
import styles from '../styles/ProfileIcon.module.css';
import {Link} from "react-router-dom";
import axios from "axios";

const ProfileIcon = () => {
    const [isExpanded, setIsExpanded] = useState(false);
    const [user, setUser] = useState({firstName: "", lastName: "", email: ""});

    useEffect(() => {
        const script = document.createElement("script");
        script.src = "https://kit.fontawesome.com/71035d1681.js";
        script.crossOrigin = "anonymous";
        script.async = true;
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        };
    }, []);

    useEffect(() => {
        axios.get('/api/user-profile')
            .then(response => {
                setUser(response.data);
            })
            .catch(error => {
                console.error("Error fetching user profile:", error);
            });
    }, []);

    const toggleCard = () => {
        setIsExpanded((prev) => !prev);
    };

    return (
        <div className={styles.profileIconContainer}>
            <button type="button" className={styles.cardBtn} onClick={toggleCard}>
                        <span className={styles.plus}>
                            <i className="fa-solid fa-user"></i>
                        </span>
                <span className={styles.btnText}></span>
            </button>
            <div className={`${styles.card} ${isExpanded ? styles.change : ''}`}>

                <div className={styles.cardBottom}>
                    <h1>{user.firstName} {user.lastName}</h1>
                    <h3>{user.email}</h3>
                    <div className={styles.socialMedia}>
                        <div className={styles.socialInfo}>
                            <Link to="/profile/orders">
                                <i className="fa-solid fa-box"></i>
                            </Link>
                            <span className={styles.num}>Orders</span>
                        </div>
                        <div className={styles.socialInfo}>
                            <Link to="/profile/security-centre">
                                <i className="fa-solid fa-lock"></i>
                            </Link>
                            <span className={styles.num}>Security</span>
                        </div>
                        <div className={styles.socialInfo}>
                            <Link to="/profile/addresses">
                                <i className="fa-solid fa-map-location"></i>
                            </Link>
                            <span className={styles.num}>Address</span>
                        </div>
                    </div>
                    <div className={styles.logout}>
                        <Link to="/logout">
                            <p>Logout</p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProfileIcon;