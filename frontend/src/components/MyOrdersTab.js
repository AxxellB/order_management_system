import React, {useEffect, useState} from 'react';
import {Link} from 'react-router-dom';
import axios from 'axios';
import '../styles/MyOrdersTab.css';
import ProfileNavbar from './ProfileNavBar';

const MyOrdersTab = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchOrders = async () => {
            try {
                const response = await axios.get('/api/user-orders');
                setOrders(response.data);
            } catch (error) {
                console.error('Error fetching orders:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchOrders();
    }, []);

    return (
        <div className="container">
            <ProfileNavbar/>

            <h2 className="mb-4">My Orders</h2>

            {loading ? (
                <div className="d-flex justify-content-center">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Loading...</span>
                    </div>
                </div>
            ) : orders.length > 0 ? (
                <table className="table">
                    <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {orders.map(order => (
                        <tr key={order.id}>
                            <td>{order.id}</td>
                            <td>{new Date(order.orderDate).toLocaleString()}</td>
                            <td>${order.totalAmount}</td>
                            <td>{order.status}</td>
                            <td>
                                <Link to={`/profile/orders/${order.id}`}>
                                    <button className="view-button">View</button>
                                </Link>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            ) : (
                <p>No orders found.</p>
            )}
        </div>
    );
};

export default MyOrdersTab;
