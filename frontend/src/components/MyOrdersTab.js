import React, {useEffect, useState} from 'react';
import {Link} from 'react-router-dom';
import axios from 'axios';
import {Table, Pagination, Spinner} from "react-bootstrap";
import '../styles/MyOrdersTab.css';
import {useAlert} from "../provider/AlertProvider";

const MyOrdersTab = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const itemsPerPage = 10;
    const {showAlert} = useAlert();

    useEffect(() => {
        const delayFetch = setTimeout(fetchOrders, 50);
        return () => clearTimeout(delayFetch);
    }, [currentPage]);

    const fetchOrders = async (page = 1) => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/user-orders?page=${page}&itemsPerPage=${itemsPerPage}`);
            setOrders(response.data.orders);
            setTotalItems(response.data.totalItems);
        } catch (error) {
            if (error.status === 404) {
                showAlert("You don't have any orders yet", "info");
            } else {
                showAlert("Failed to fetch orders. Please try again.", "error");
            }
        } finally {
            setLoading(false);
        }
    };

    const totalPages = Math.ceil(totalItems / itemsPerPage);

    return (
        <div className="container">
            <h2 className="mb-4">My Orders</h2>

            {loading ? (
                <div className="d-flex justify-content-center">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : orders.length > 0 ? (
                <>
                    <Table responsive striped bordered hover className="mb-4">
                        <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Invoice</th>
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
                                <td>
                                    <a
                                        href={`/api/invoice/download/${order.id}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="download-button"
                                    >
                                        <button className="view-button">
                                            <i className="fa-solid fa-download"></i>
                                        </button>
                                    </a>
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </Table>

                    <Pagination className="justify-content-center">
                        {Array.from({length: totalPages}, (_, index) => (
                            <Pagination.Item
                                key={index + 1}
                                active={index + 1 === currentPage}
                                onClick={() => setCurrentPage(index + 1)}
                            >
                                {index + 1}
                            </Pagination.Item>
                        ))}
                    </Pagination>
                </>
            ) : (
                <p>You don't have any orders yet!</p>
            )}
        </div>
    );
};

export default MyOrdersTab;
