import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import '../styles/MyOrdersTab.css'

const MyOrdersDetailsTab = () => {
    const { id } = useParams();
    const [order, setOrder] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOrderDetail = async () => {
            try {
                const response = await axios.get(`/api/order/${id}`);
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
        <div className="container">
            <h2 className="details-header">Order Details</h2>
            <h2>Order ID: {order.id}</h2>
            <h4>Order Date: {new Date(order.orderDate).toLocaleString()}</h4>
            <h4>Total Amount: ${order.totalAmount}</h4>
            <h4>Status: {order.status}</h4>

            <h3>Address</h3>
            <p>{order.address.line}</p>
            {order.address.line2 && <p>{order.address.line2}</p>}
            <p>{order.address.city}, {order.address.country} - {order.address.postcode}</p>

            <h3>Products</h3>
            <div className="order-products">
                <table className="table">
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
            </div>
            <button className="back-button" onClick={() => window.history.back()}>Back</button>

        </div>
    );
};

export default MyOrdersDetailsTab;
