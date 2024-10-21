import React, {useState, useEffect} from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../styles/OrderManagementPage.css';
import EditOrderForm from '../components/EditOrderForm';
import {Link} from "react-router-dom";
import axios from 'axios';

const OrderList = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [editingOrderId, setEditingOrderId] = useState(null);

    useEffect(() => {
        const fetchOrders = async () => {
            try {
                const response = await axios.get('http://localhost/api/orders');
                setOrders(response.data);
                setLoading(false);
            } catch (error) {
                setError(error.message);
                setLoading(false);
            }
        };

        fetchOrders();
    }, []);

    const deleteOrder = async (orderId) => {
        const confirmDelete = window.confirm('Are you sure you want to delete this order?');
        if (!confirmDelete) return;

        try {
            const response = await axios.delete(`http://localhost/api/order/${orderId}`);
            if (response.status !== 200) {
                throw new Error('Failed to delete order.');
            }

            setOrders(orders.filter(order => order.id !== orderId));
        } catch (error) {
            console.error('Error deleting order:', error);
        }
    };

    const startEditing = (orderId) => {
        setEditingOrderId(orderId);
    };

    const finishEditing = () => {
        setEditingOrderId(null);
    };

    if (loading) return <div className="text-center mt-5">Loading...</div>;
    if (error) return <div className="text-center text-danger mt-5">Error: {error}</div>;

    return (
        <div className="container mt-5">
            {editingOrderId ? (
                <EditOrderForm orderId={editingOrderId} onFinishEditing={finishEditing}/>
            ) : (
                <>
                    <h1>Orders</h1>
                    <table className="table table-striped">
                        <thead>
                        <tr>
                            <th>User</th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {orders.length > 0 ? (
                            orders.map((order) => (
                                <tr key={order.id}>
                                    <td>{order.userId}</td>
                                    <td>{order.id}</td>
                                    <td>{new Date(order.orderDate).toLocaleString()}</td>
                                    <td>${order.totalAmount}</td>
                                    <td>{order.paymentMethod}</td>
                                    <td>{order.status}</td>
                                    <td>
                                        <button
                                            className="btn btn-primary"
                                            onClick={() => startEditing(order.id)}
                                        >
                                            <Link to={`/admin/order/${order.id}`}
                                                  style={{color: 'white', textDecoration: 'none'}}>Edit</Link>
                                        </button>
                                        <button
                                            className="btn btn-danger"
                                            onClick={() => deleteOrder(order.id)}
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="6" className="text-center">No orders found.</td>
                            </tr>
                        )}
                        </tbody>
                    </table>
                </>
            )}
        </div>
    );
};

export default OrderList;
