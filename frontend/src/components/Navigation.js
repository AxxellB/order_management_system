import React, {useEffect} from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from "../provider/AuthProvider";
import styles from '../styles/Navigation.module.css';
import AdminSidebar from "./AdminSidebar";
import ProfileIcon from "./ProfileIcon";

const Navigation = () => {
    const { user, isAdmin } = useAuth();

    useEffect(() => {
        const script = document.createElement("script");
        script.src = "https://kit.fontawesome.com/71035d1681.js";
        script.crossOrigin = "anonymous";
        script.async = true;
        document.body.appendChild(script);
    }, []);

    return (
        <nav className={`navbar navbar-expand-md ${styles.navbar}`}>
            {isAdmin && <AdminSidebar />}
            <div className="container d-flex align-items-center justify-content-between">
                <Link className={`navbar-brand ${styles.brand}`} to="/">ECommerce</Link>

                <button className={`navbar-toggler ${styles.navbarToggler}`} type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span className={`navbar-toggler-icon ${styles.navbarTogglerIcon}`}></span>
                </button>

                <div className="collapse navbar-collapse" id="navbarNav">
                    <ul className="navbar-nav me-auto d-flex align-items-center">
                        <li className="nav-item">
                            <Link className={`nav-link ${styles.navLink}`} to="/">Home</Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${styles.navLink}`} to="/categories">Categories</Link>
                        </li>

                        {user && (
                            <li className="nav-item">
                                <Link className={`nav-link ${styles.navLink}`} to="/profile">Profile</Link>
                            </li>
                        )}
                    </ul>

                    <ul className="navbar-nav ms-auto d-flex align-items-center">
                        {user ? (
                            <>
                                <li className="nav-item">
                                    <ProfileIcon />
                                </li>
                                <li className="nav-item">
                                    <Link className={`nav-link ${styles.navLink}`} to="/basket">
                                        <i className="fa-solid fa-bag-shopping"></i>
                                    </Link>
                                </li>
                            </>
                        ) : (
                            <>
                                <li className="nav-item"><Link className={`nav-link ${styles.navLink}`} to="/login">Login</Link></li>
                                <li className="nav-item"><Link className={`nav-link ${styles.navLink}`} to="/register">Register</Link></li>
                            </>
                        )}
                    </ul>
                </div>
            </div>
        </nav>
    );
};

export default Navigation;
