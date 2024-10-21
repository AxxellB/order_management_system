import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, Link, useNavigate } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';

const ProductDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchProduct();
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

        if (confirmDelete){
            try {
                await axios.delete(`http://localhost/api/products/${id}`, {
                    data: { action: 'delete' },
                });
                alert('Product successfully soft deleted');
                navigate('/admin/products');
            } catch (err) {
                alert('Error deleting product');
            }
        }

    };

    const handleRestore = async () => {
        const confirmRestore = window.confirm('Are you sure you want to restore this product?');

        if(confirmRestore){
            try {
                await axios.delete(`http://localhost/api/products/${id}`, {data: { action: 'restore' },});
                alert('Product successfully restored');
                navigate('/admin/products')
            } catch (err) {
                alert('Error restoring product');
            }
        }

    };

    if (loading) return <div>Loading...</div>;
    if (error) return <div>Error: {error}</div>;

    const productPrice = product.price ? parseFloat(product.price).toFixed(2) : 'N/A';

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Product Details</h1>

            <table className="table table-bordered">
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
                </tbody>
            </table>

            <div className="d-flex justify-content-between mt-4">
                <div className="d-flex justify-content-start">
                    {product.deletedAt ? (
                        <button className="btn btn-success me-2" onClick={handleRestore}>
                            Restore
                        </button>
                    ) : (
                        <>
                            <button className="btn btn-danger me-2" onClick={handleSoftDelete}>
                                Delete
                            </button>

                        </>
                    )}
                    <Link to={`/admin/products/edit/${product.id}`} className="btn btn-warning ">
                        Edit
                    </Link>
                </div>
                <div>
                    <button className="btn btn-secondary" onClick={() => navigate('/admin/products')}>
                        Back
                    </button>
                </div>
            </div>
        </div>
    );
};

export default ProductDetails;
