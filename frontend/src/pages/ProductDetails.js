import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, Link, useNavigate } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
import { useAlert } from "../provider/AlertProvider";
import styles from '../styles/ProductDetails.module.css';

const ProductDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const { showAlert } = useAlert();

    useEffect(() => {
        const delayFetchProduct = setTimeout(() => {
            fetchProduct();
        }, 50);

        return () => clearTimeout(delayFetchProduct);
    }, []);

    const fetchProduct = async () => {
        try {
            const response = await axios.get(`http://localhost/api/products/${id}`);
            setProduct(response.data);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const handleSoftDelete = async () => {
        const confirmDelete = window.confirm('Are you sure you want to delete this product?');
        if (confirmDelete) {
            try {
                await axios.delete(`http://localhost/api/products/${id}`, { data: { action: 'delete' } });
                showAlert('Product successfully soft deleted', "success");
                navigate('/admin/products');
            } catch (err) {
                showAlert('Error deleting product', "error");
            }
        }
    };

    const handleRestore = async () => {
        const confirmRestore = window.confirm('Are you sure you want to restore this product?');
        if (confirmRestore) {
            try {
                await axios.delete(`http://localhost/api/products/${id}`, { data: { action: 'restore' } });
                showAlert('Product successfully restored', "success");
                navigate('/admin/products');
            } catch (err) {
                showAlert('Error restoring product', "error");
            }
        }
    };

    if (loading) {
        return (
            <div className={`${styles.message}`}>
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (error) return <div className={`${styles.message} ${styles.error}`}>Error: {error}</div>;

    const productPrice = product.price ? parseFloat(product.price).toFixed(2) : 'N/A';

    return (
        <div className={styles.container}>
            <h1 className={styles.title}>Product Details</h1>

            <table className={`table ${styles.table} table-bordered`}>
                <tbody>
                <tr>
                    <th>Name</th>
                    <td>{product.name}</td>
                </tr>
                <tr>
                    <th>Price</th>
                    <td>${productPrice}</td>
                </tr>
                <tr>
                    <th>Stock Quantity</th>
                    <td>{product.stockQuantity}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{product.description}</td>
                </tr>
                <tr>
                    <th>Categories</th>
                    <td>
                        {product.categories && product.categories.length > 0 ? (
                            <ul className={styles.categoryList}>
                                {product.categories.map(category => (
                                    <li key={category.id} className={styles.categoryListItem}>
                                        {category.name}
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>No categories assigned</p>
                        )}
                    </td>
                </tr>
                </tbody>
            </table>

            <div className={styles.buttonContainer}>
                <div className={styles.buttonGroup}>
                    {product.deletedAt ? (
                        <button className={`btn btn-success ${styles.button}`} onClick={handleRestore}>
                            Restore
                        </button>
                    ) : (
                        <button className={`btn btn-danger ${styles.button}`} onClick={handleSoftDelete}>
                            Delete
                        </button>
                    )}
                    <Link to={`/admin/products/edit/${product.id}`} className={`btn btn-warning ${styles.button}`}>
                        Edit
                    </Link>
                </div>
                <button className={`btn ${styles.backButton}`} onClick={() => navigate('/admin/products')}>
                    Back
                </button>
            </div>
        </div>
    );
};

export default ProductDetails;
