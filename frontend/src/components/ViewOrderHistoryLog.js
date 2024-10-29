import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, Link } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';

const ViewOrderLog = () => {
    const { id } = useParams();
    const [log, setLog] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchLog = async () => {
            try {
                const response = await axios.get(`/api/order-history-logs/${id}`);
                setLog(response.data);
                setLoading(false);
            } catch (error) {
                setError(error.message);
                setLoading(false);
            }
        };

        fetchLog();
    }, [id]);

    const formatValue = (value) => {
        if (typeof value === 'object') {
            return Object.entries(value)
                .map(([key, val]) => `${key}: ${val}`)
                .join(', ');
        }
        return value;
    };

    if (loading) return <div className="d-flex justify-content-center">
        <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Loading...</span>
        </div>
    </div>
        ;
    if (error) return <div className="text-center text-danger mt-5">Error: {error}</div>;

    return log ? (
        <div className="container mt-5">
            <h1>Order Log Details</h1>
            <table className="table table-bordered mt-3">
                <thead>
                <tr>
                    <th>User ID</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                    <th>Date/Time</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{log.userId}</td>
                    <td>{formatValue(log.oldValue)}</td>
                    <td>{formatValue(log.newValue)}</td>
                    <td>{new Date(log.timestamp).toLocaleString()}</td>
                </tr>
                </tbody>
            </table>
            <Link to="/admin/order-history-logs" className="btn btn-secondary mt-3">Back to Logs</Link>
        </div>
    ) : null;
};

export default ViewOrderLog;
