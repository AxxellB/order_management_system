import React, {useState, useEffect} from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import axios from 'axios';
import {Link} from 'react-router-dom';

const OrderAuditLogs = () => {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const limit = 15;

    useEffect(() => {
        fetchLogs();
    }, [currentPage]);

    const fetchLogs = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/order-history-logs', {
                params: {
                    page: currentPage,
                    limit: limit,
                },
            });
            const {data, totalPages: total} = response.data;

            setLogs(data);
            setTotalPages(total);
            setLoading(false);
        } catch (error) {
            setError(error.message);
            setLoading(false);
        }
    };

    const handlePageChange = (newPage) => {
        if (newPage > 0 && newPage <= totalPages) {
            setCurrentPage(newPage);
        }
    };

    if (loading) return (
        <div className="d-flex justify-content-center">
            <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );

    if (error) return <div className="text-center text-danger mt-5">Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1>Order History Logs</h1>
            {logs.length > 0 ? (
                <>
                    <table className="table table-striped">
                        <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Change Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {logs.map((log) => (
                            <tr key={log.id}>
                                <td>{log.orderId}</td>
                                <td>{log.userId}</td>
                                <td>{log.changeType}</td>
                                <td>{new Date(log.timestamp).toLocaleString()}</td>
                                <td>
                                    <Link to={`/admin/order-history-logs/${log.id}`} className="btn btn-primary">
                                        View
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                    <Pagination
                        currentPage={currentPage}
                        totalPages={totalPages}
                        onPageChange={handlePageChange}
                    />
                </>
            ) : (
                <div className="text-center mt-4">No audit logs available.</div>
            )}
        </div>
    );
};

const Pagination = ({currentPage, totalPages, onPageChange}) => {
    const pageNumbers = [];
    const maxVisiblePages = 5;

    for (let i = 1; i <= totalPages; i++) {
        if (i <= maxVisiblePages || i === totalPages || i === currentPage || (i === currentPage - 1 && currentPage > 3) || (i === currentPage + 1 && currentPage < totalPages - 2)) {
            pageNumbers.push(i);
        } else if (pageNumbers[pageNumbers.length - 1] !== '...') {
            pageNumbers.push('...');
        }
    }

    return (
        <div className="d-flex justify-content-between align-items-center mt-3">
            <button
                className="btn btn-secondary"
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage === 1}
            >
                Previous
            </button>
            <div>
                {pageNumbers.map((number, index) =>
                    number === '...' ? (
                        <span key={index} className="mx-1">...</span>
                    ) : (
                        <button
                            key={index}
                            className={`btn ${number === currentPage ? 'btn-primary' : 'btn-light'} mx-1`}
                            onClick={() => onPageChange(number)}
                        >
                            {number}
                        </button>
                    )
                )}
            </div>
            <button
                className="btn btn-secondary"
                onClick={() => onPageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
            >
                Next
            </button>
            <button
                className="btn btn-info mx-2"
                onClick={() => onPageChange(totalPages)}
                disabled={currentPage === totalPages}
            >
                Last
            </button>
        </div>
    );
};

export default OrderAuditLogs;
