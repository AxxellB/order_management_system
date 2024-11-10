import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { useAlert } from "../provider/AlertProvider";
import styles from '../styles/CategoriesList.module.css';

const CategoriesList = () => {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [message, setMessage] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const limit = 5;
    const { showAlert } = useAlert();

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
            const { data, totalPages, currentPage: page } = response.data;

            setCategories(data);
            setTotalItems(totalPages * limit);
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
                await axios.delete(`http://localhost/api/categories/${id}`);
                showAlert("Category deleted successfully", "success");
                fetchCategories();
            } catch (error) {
                const errorMsg = error.response?.data?.error || "Error deleting category";
                showAlert(errorMsg, "error");
            }
        }
    };

    const totalPages = Math.ceil(totalItems / limit);

    if (error) return <div>Error: {error}</div>;

    return (
        <div className={styles.container}>
            <h1 className={styles.title}>Category Management</h1>

            {message && (
                <div className={`${styles.alert} ${message.type === 'success' ? 'alert-success' : 'alert-danger'}`}>
                    {message.text}
                </div>
            )}

            {loading && <div className="text-center mb-4">Loading...</div>}

            {!loading && (
                <>
                    <table className={`table table-striped ${styles.table}`}>
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
                                        <div className={styles.tableActions}>
                                            <Link to={`/admin/categories/edit/${category.id}`} className="btn btn-warning btn-sm me-2">Edit</Link>
                                            <button className="btn btn-danger btn-sm me-2" onClick={() => handleDelete(category.id)}>Delete</button>
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

                    <nav className="mt-4">
                        <ul className="pagination justify-content-center">
                            {[...Array(totalPages)].map((_, index) => (
                                <li
                                    key={index}
                                    className={`page-item ${index + 1 === currentPage ? 'active' : ''}`}
                                    onClick={() => setCurrentPage(index + 1)}
                                >
                                    <span className="page-link">{index + 1}</span>
                                </li>
                            ))}
                        </ul>
                    </nav>

                    <div className="text-center mt-4">
                        <Link to="/admin/categories/new" className={styles.createButton}>Create new</Link>
                    </div>
                </>
            )}
        </div>
    );
};

export default CategoriesList;
