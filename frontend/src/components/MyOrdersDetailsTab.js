import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import styles from '../styles/MyOrdersDetailsTab.module.css';

const MyOrdersDetailsTab = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [order, setOrder] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOrderDetail = async () => {
            try {
                const response = await axios.get(`/api/user-order/${id}`);
                setOrder(response.data);
            } catch (error) {
                setError('Failed to load order details');
            }
        };

        fetchOrderDetail();
    }, [id]);

    if (error) return <p className="text-danger">{error}</p>;

    if (!order) return (
        <div className="d-flex justify-content-center">
            <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );

    return (
        <div className={styles.container}>
            <h1 className={styles.title}>Order Details</h1>
            <table className={`table ${styles.table} table-bordered`}>
                <tbody>
                <tr>
                    <th>Order ID</th>
                    <td>{order.id}</td>
                </tr>
                <tr>
                    <th>Order Date</th>
                    <td>{new Date(order.orderDate).toLocaleString()}</td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td>${order.totalAmount}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{order.status}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>
                        <p>{order.address.line}</p>
                        {order.address.line2 && <p>{order.address.line2}</p>}
                        <p>{order.address.city}, {order.address.country} - {order.address.postcode}</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <h3>Products</h3>
            <table className={`table ${styles.table} table-bordered`}>
                <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price Per Unit</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                {order.orderProducts.map(product => (
                    <tr key={product.id}>
                        <td>{product.name}</td>
                        <td>{product.quantity}</td>
                        <td>${product.pricePerUnit}</td>
                        <td>${product.subtotal}</td>
                    </tr>
                ))}
                </tbody>
            </table>

            <button className={`btn ${styles.backButton}`} onClick={() => navigate(-1)}>Back</button>
        </div>
    );
};

export default MyOrdersDetailsTab;
