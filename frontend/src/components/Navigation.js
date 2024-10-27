import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from "../provider/AuthProvider";
import styles from '../styles/Navigation.module.css';
import AdminSidebar from "./AdminSidebar";

const Navigation = () => {
    const { user, isAdmin } = useAuth();

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

                        {/* OLD ADMIN */}
                        {/*{isAdmin && (*/}
                        {/*    <li className="nav-item dropdown">*/}
                        {/*        <a className={`nav-link dropdown-toggle ${styles.navLink}`} href="#" id="adminDropdown" role="button"*/}
                        {/*           data-bs-toggle="dropdown" aria-expanded="false">*/}
                        {/*            Admin*/}
                        {/*        </a>*/}
                        {/*        <ul className={`dropdown-menu ${styles.dropdownMenu}`} aria-labelledby="adminDropdown">*/}
                        {/*            <li><Link className={`dropdown-item ${styles.dropdownItem}`} to="/admin/products">Product Edit</Link></li>*/}
                        {/*            <li><Link className={`dropdown-item ${styles.dropdownItem}`} to="/admin/categories">Category Edit</Link></li>*/}
                        {/*            <li><Link className={`dropdown-item ${styles.dropdownItem}`} to="/admin/orders">Orders</Link></li>*/}
                        {/*        </ul>*/}
                        {/*    </li>*/}
                        {/*)}*/}

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
                                    <Link className={`nav-link ${styles.navLink}`} to="/basket">
                                        <i className={`bi bi-basket ${styles.basketIcon}`}></i>
                                    </Link>
                                </li>
                                <li className="nav-item">
                                    <Link className={`nav-link ${styles.navLink}`} to="/profile">{user.firstName}</Link>
                                </li>
                                <li className="nav-item">
                                    <Link className={`nav-link ${styles.navLink}`} to="/logout">Logout</Link>
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
