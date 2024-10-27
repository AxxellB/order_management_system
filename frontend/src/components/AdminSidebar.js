import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';
import styles from '../styles/AdminSidebar.module.css';

const AdminSidebar = () => {
    useEffect(() => {
        const script = document.createElement("script");
        script.src = "https://kit.fontawesome.com/71035d1681.js";
        script.crossOrigin = "anonymous";
        script.async = true;
        document.body.appendChild(script);
    }, []);

    return (
        <>
            <div className={styles.adminSidebarWrapper}>
                <section className={styles.adminSidebarBg}></section>
                <label>
                    <input type="checkbox" className={styles.adminSidebarInput} />
                    <div className={styles.adminSidebarToggle}>
                        <span className={`${styles.adminSidebarToggleCommon} ${styles.adminSidebarTopLine}`}></span>
                        <span className={`${styles.adminSidebarToggleCommon} ${styles.adminSidebarMiddleLine}`}></span>
                        <span className={`${styles.adminSidebarToggleCommon} ${styles.adminSidebarBottomLine}`}></span>
                        <span className={styles.adminSidebarImageLineIcon}>
                            <i className="fa-solid fa-user-tie"></i>
                        </span>
                    </div>

                    <div className={styles.adminSidebar}>
                        <h1>Admin</h1>
                        <ul className={styles.adminSidebarList}>
                            <li className={styles.adminSidebarItem} style={{ '--i': 1 }}>
                                <Link to="/admin/products" className={styles.adminSidebarLink}>
                                    <i className="fa-solid fa-cube"></i> Products
                                </Link>
                            </li>
                            <li className={styles.adminSidebarItem} style={{ '--i': 2 }}>
                                <Link to="/admin/categories" className={styles.adminSidebarLink}>
                                    <i className="fa-solid fa-shapes"></i> Categories
                                </Link>
                            </li>
                            <li className={styles.adminSidebarItem} style={{ '--i': 3 }}>
                                <Link to="/admin/orders" className={styles.adminSidebarLink}>
                                    <i className="fa-regular fa-rectangle-list"></i> Orders
                                </Link>
                            </li>
                            <li className={styles.adminSidebarItem} style={{ '--i': 4 }}>
                                <Link to="/admin/logs" className={styles.adminSidebarLink}>
                                    <i className="fas fa-tv"></i> Logs
                                </Link>
                            </li>
                        </ul>
                    </div>
                </label>
            </div>
        </>
    );
};

export default AdminSidebar;
