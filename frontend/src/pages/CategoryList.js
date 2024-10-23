import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

const CategoriesList = () => {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [message, setMessage] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const limit = 3;

    useEffect(() => {
        fetchCategories();
    }, [currentPage]);

    useEffect(() => {
        if (message) {
            const timer = setTimeout(() => {
                setMessage(null);
            }, 2000);

            return () => clearTimeout(timer);
        }
    }, [message]);

    const fetchCategories = async () => {
        try {
            setLoading(true);
            const response = await axios.get(`http://localhost/api/categories/list?page=${currentPage}&limit=${limit}`);
            const { data, totalPages: total, currentPage: page } = response.data;

            setCategories(data);
            setTotalPages(total);
            setCurrentPage(page);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        const confirmDelete = window.confirm('Are you sure you want to delete this category?');

        if (confirmDelete) {
            try {
                const response = await axios.delete(`http://localhost/api/categories/${id}`);
                setMessage({ text: response.data.message, type: 'success' });
                fetchCategories();
            } catch (error) {
                if (error.response && error.response.data.error) {
                    setMessage({ text: error.response.data.error, type: 'error' });
                } else {
                    console.error('Error deleting category:', error);
                    setMessage({ text: 'An unexpected error occurred.', type: 'error' });
                }
            }
        }
    };

    const handlePageChange = (newPage) => {
        if (newPage > 0 && newPage <= totalPages) {
            setCurrentPage(newPage);
        }
    };

    if (error) return <div>Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Category Management</h1>

            {message && (
                <div className={`alert ${message.type === 'success' ? 'alert-success' : 'alert-danger'}`}>
                    {message.text}
                </div>
            )}

            {loading && <div className="text-center mb-4">Loading...</div>}

            {!loading && (
                <>
                    <table className="table table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th className="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {categories.length > 0 ? (
                            categories.map(category => (
                                <tr key={category.id}>
                                    <td>{category.name}</td>
                                    <td className="text-end">
                                        <div className="btn-group">
                                            <Link to={`/admin/categories/edit/${category.id}`}
                                                  className="btn btn-warning btn-sm me-2">Edit</Link>
                                            <button className="btn btn-danger btn-sm me-2"
                                                    onClick={() => handleDelete(category.id)}>Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="2" className="text-center">No categories found.</td>
                            </tr>
                        )}
                        </tbody>
                    </table>

                    <div className="d-flex justify-content-between align-items-center">
                        <button
                            className="btn btn-secondary"
                            onClick={() => handlePageChange(currentPage - 1)}
                            disabled={currentPage === 1}
                        >
                            Previous
                        </button>
                        <span>Page {currentPage} of {totalPages}</span>
                        <button
                            className="btn btn-secondary"
                            onClick={() => handlePageChange(currentPage + 1)}
                            disabled={currentPage === totalPages}
                        >
                            Next
                        </button>
                    </div>
                    <div className="text-center mt-4">
                        <Link to="/admin/categories/new" className="btn btn-success">Create new</Link>
                    </div>
                </>

            )}


        </div>
    );
};

export default CategoriesList;
