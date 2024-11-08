import React from 'react';
import {Link} from 'react-router-dom';
import styles from '../styles/OrderSuccessPage.module.css';

const OrderSuccessPage = () => {
    return (
        <div className={styles.orderSuccessPage}>
            <h2 className={styles.orderSuccessTick}><i className="fa-solid fa-circle-check"></i></h2>
            <h2 className={styles.orderSuccessTitle}>Order placed successfully </h2>
            <p className={styles.orderSuccessMessage}>An order confirmation email has been sent to you!</p>
            <p className={styles.orderSuccessMessage}>Thank you for your order!</p>
            <Link to="/" className={styles.backToHome}>Return to Home</Link>
        </div>
    );
};

export default OrderSuccessPage;
