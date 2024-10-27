import React, {useState, useEffect} from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../styles/OrderManagementPage.module.css';
import EditOrderForm from '../components/EditOrderForm';
import {Link} from 'react-router-dom';
import axios from 'axios';

const OrderList = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [editingOrderId, setEditingOrderId] = useState(null);
    const [status, setStatus] = useState('active');

    useEffect(() => {
        const fetchOrders = async () => {
            setLoading(true);
            setError(null);
            try {
                const response = await axios.get(`/api/orders?status=${status}`);
                setOrders(response.data);
                setLoading(false);
            } catch (error) {
                setError(error.message);
                setLoading(false);
            }
        };

        fetchOrders();
    }, [status]);

    const deleteOrder = async (orderId) => {
        const confirmDelete = window.confirm('Are you sure you want to delete this order?');
        if (!confirmDelete) return;

        try {
            const response = await axios.delete(`/api/order/${orderId}`);
            if (response.status !== 200) {
                throw new Error('Failed to delete order.');
            }

            setOrders(orders.filter(order => order.id !== orderId));
        } catch (error) {
            console.error('Error deleting order:', error);
        }
    };

    const restoreOrder = async (orderId) => {
        const confirmRestore = window.confirm('Are you sure you want to restore this order?');
        if (!confirmRestore) return;

        try {
            const response = await axios.delete(`/api/order/${orderId}`);
            if (response.status !== 200) {
                throw new Error('Failed to restore order.');
            }

            setOrders(orders.filter(order => order.id !== orderId));
        } catch (error) {
            console.error('Error restoring order:', error);
        }
    };

    const handleStatusChange = (newStatus) => {
        if (status !== newStatus) {
            setStatus(newStatus);
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
                    <h1>Order Management</h1>

                    <div className="text-center mb-4">
                        <button
                            onClick={() => handleStatusChange('active')}
                            className={`btn ${status === 'active' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            Active Orders
                        </button>
                        <button
                            onClick={() => handleStatusChange('deleted')}
                            className={`btn ${status === 'deleted' ? 'btn-primary' : 'btn-secondary'}`}
                        >
                            Deleted Orders
                        </button>
                    </div>

                    {orders.length > 0 ? (
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
                            {orders.map((order) => (
                                <tr key={order.id}>
                                    <td>{order.userId}</td>
                                    <td>{order.id}</td>
                                    <td>{new Date(order.orderDate).toLocaleString()}</td>
                                    <td>${order.totalAmount}</td>
                                    <td>{order.paymentMethod}</td>
                                    <td>{order.status}</td>
                                    <td>
                                        {status === 'active' ? (
                                            <>
                                                <button className="btn btn-primary">
                                                    <Link to={`/admin/order/${order.id}`}
                                                          style={{color: 'white', textDecoration: 'none'}}>
                                                        Edit
                                                    </Link>
                                                </button>
                                                <button className="btn btn-danger"
                                                        onClick={() => deleteOrder(order.id)}>
                                                    Delete
                                                </button>
                                            </>
                                        ) : (
                                            <button className="btn btn-success" onClick={() => restoreOrder(order.id)}>
                                                Restore
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    ) : (
                        <div className="text-center mt-4">
                            {status === 'active' ? "There are no active orders." : "There are no deleted orders."}
                        </div>
                    )}
                </>
            )}
        </div>
    );
};

export default OrderList;
