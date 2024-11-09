import React, {useState, useEffect, useCallback} from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../styles/OrderManagementPage.module.css';
import {Link} from 'react-router-dom';
import axios from 'axios';
import {debounce} from "../components/debounce";
import {Pagination, Spinner, OverlayTrigger, Tooltip} from 'react-bootstrap';
import {useAlert} from "../provider/AlertProvider";

const OrderList = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [status, setStatus] = useState('active');
    const [page, setPage] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const itemsPerPage = 10;
    const [searchTerm, setSearchTerm] = useState("");
    const {showAlert} = useAlert();

    useEffect(() => {
        fetchOrders(page);
    }, [page, status, searchTerm]);

    const fetchOrders = async (currentPage) => {
        setLoading(true);
        setError(null);
        try {
            const queryParams = new URLSearchParams({
                status,
                page: currentPage,
                itemsPerPage,
                search: searchTerm,
            });
            const response = await axios.get(`/api/orders?${queryParams.toString()}`);
            setOrders(response.data.orders || []);
            setTotalItems(response.data.totalItems || 0);
        } catch (error) {
            showAlert("Failed to fetch orders. Please try again.", "error");
        } finally {
            setLoading(false);
        }
    };

    const deleteOrder = async (orderId) => {
        const confirmDelete = window.confirm('Are you sure you want to delete this order?');
        if (!confirmDelete) return;

        try {
            await axios.delete(`/api/order/${orderId}`);
            setOrders(orders.filter(order => order.id !== orderId));
            showAlert("Order deleted successfully", 'success');
        } catch (error) {
            showAlert("Error occurred while deleting order. Please try again", 'error');
        }
    };

    const restoreOrder = async (orderId) => {
        const confirmRestore = window.confirm('Are you sure you want to restore this order?');
        if (!confirmRestore) return;

        try {
            const response = await axios.delete(`/api/order/${orderId}`);
            if (response.status !== 200) {
                showAlert("An error occurred. Please try again", 'error');
                return;
            }

            setOrders(orders.filter(order => order.id !== orderId));
        } catch (error) {
            showAlert("Error occurred while restoring order. Please try again", 'error');
        }
    };

    const debouncedSearch = useCallback(
        debounce((term) => {
            setSearchTerm(term);
            setPage(1);
        }, 300),
        []
    );

    const handleSearchChange = (e) => {
        debouncedSearch(e.target.value);
    };

    const handleStatusChange = (newStatus) => {
        setStatus(newStatus);
        setPage(1);
    };

    const totalPages = Math.ceil(totalItems / itemsPerPage);

    return (
        <div className="container mt-5">
            <h1>Order Management</h1>

            <input
                type="text"
                placeholder="Search by user email"
                className="form-control mb-3"
                onChange={handleSearchChange}
            />

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

            {loading ? (
                <div className="text-center mt-5">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : error ? (
                <div className="text-center text-danger mt-5">{error}</div>
            ) : (
                <>
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
                                                {order.status === 'cancelled' ? (
                                                    <OverlayTrigger
                                                        overlay={<Tooltip>Edit disabled for cancelled orders</Tooltip>}
                                                    >
                                                        <button className="btn btn-secondary me-2" disabled>
                                                            Edit
                                                        </button>
                                                    </OverlayTrigger>
                                                ) : (
                                                    <Link to={`/admin/order/${order.id}`}
                                                          className="btn btn-primary me-2">
                                                        Edit
                                                    </Link>
                                                )}
                                                <button
                                                    className="btn btn-danger"
                                                    onClick={() => deleteOrder(order.id)}
                                                >
                                                    Delete
                                                </button>
                                            </>
                                        ) : (
                                            <button
                                                className="btn btn-success"
                                                onClick={() => restoreOrder(order.id)}
                                            >
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
                    <Pagination className="justify-content-center mt-4">
                        {Array.from({length: totalPages}, (_, index) => (
                            <Pagination.Item
                                key={index + 1}
                                active={index + 1 === page}
                                onClick={() => setPage(index + 1)}
                            >
                                {index + 1}
                            </Pagination.Item>
                        ))}
                    </Pagination>
                </>
            )}
        </div>
    );
};

export default OrderList;