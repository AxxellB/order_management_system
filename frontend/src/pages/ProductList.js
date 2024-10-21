import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';

const ProductsList = () => {
    const navigate = useNavigate();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [status, setStatus] = useState('active');
    const [filters, setFilters] = useState({
        category: '',
        minPrice: '',
        maxPrice: '',
        minStock: '',
        maxStock: ''
    });
    const [categories, setCategories] = useState([]);

    useEffect(() => {
        fetchProducts();
        fetchCategories();
    }, [status]);

    const fetchProducts = async () => {
        try {
            setLoading(true);
            const queryParams = new URLSearchParams({ status });
            if (filters.category) queryParams.append('category', filters.category);
            if (filters.minPrice) queryParams.append('minPrice', filters.minPrice);
            if (filters.maxPrice) queryParams.append('maxPrice', filters.maxPrice);
            if (filters.minStock) queryParams.append('minStock', filters.minStock);
            if (filters.maxStock) queryParams.append('maxStock', filters.maxStock);
            const response = await axios.get(`http://localhost/api/products/list?${queryParams.toString()}`);
            setProducts(Array.isArray(response.data) ? response.data : []);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://localhost/api/categories/');
            setCategories(response.data);
        } catch (err) {
            console.error('Error fetching categories:', err);
        }
    };

    const restoreProduct = async (id) => {
        const confirmRestore = window.confirm('Are you sure you want to restore this product?');
        if (confirmRestore) {
            try {
                await axios.delete(`http://localhost/api/products/${id}`, {
                    data: { action: 'restore' },
                });
                alert('Product restored');
                fetchProducts();
            } catch (error) {
                console.error('Error restoring product', error);
            }
        }
    };

    const handleStatusChange = (newStatus) => {
        if (status !== newStatus) setStatus(newStatus);
    };

    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters({ ...filters, [name]: value });
    };

    const applyFilters = () => fetchProducts();

    if (error) return <div>Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Product Management</h1>
            <div className="row">
                <div className="col-md-2">
                    <div className="card shadow-sm p-3 mb-4">
                        <h5>Filters</h5>
                        <div className="mb-3">
                            <label className="form-label">Category</label>
                            <select
                                name="category"
                                value={filters.category}
                                onChange={handleFilterChange}
                                className="form-control"
                            >
                                <option value="">Select category</option>
                                {categories.length > 0 ? (
                                    categories.map((category) => (
                                        <option key={category.id} value={category.id}>
                                            {category.name}
                                        </option>
                                    ))
                                ) : (
                                    <option value="">No categories available</option>
                                )}
                            </select>
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Min Price</label>
                            <input
                                type="number"
                                name="minPrice"
                                value={filters.minPrice}
                                onChange={handleFilterChange}
                                className="form-control"
                                placeholder="Enter min price"
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Max Price</label>
                            <input
                                type="number"
                                name="maxPrice"
                                value={filters.maxPrice}
                                onChange={handleFilterChange}
                                className="form-control"
                                placeholder="Enter max price"
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Min Stock</label>
                            <input
                                type="number"
                                name="minStock"
                                value={filters.minStock}
                                onChange={handleFilterChange}
                                className="form-control"
                                placeholder="Enter min stock"
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Max Stock</label>
                            <input
                                type="number"
                                name="maxStock"
                                value={filters.maxStock}
                                onChange={handleFilterChange}
                                className="form-control"
                                placeholder="Enter max stock"
                            />
                        </div>
                        <button className="btn btn-primary w-100" onClick={applyFilters}>
                            Apply Filters
                        </button>
                    </div>
                </div>

                <div className="col-md-10">
                    <div className="text-center mb-4">
                        <button onClick={() => handleStatusChange('active')} className={`btn ${status === 'active' ? 'btn-primary' : 'btn-secondary'}`}>
                            Active Products
                        </button>
                        <button onClick={() => handleStatusChange('deleted')} className={`btn ${status === 'deleted' ? 'btn-primary' : 'btn-secondary'}`}>
                            Deleted Products
                        </button>
                    </div>

                    {loading && <div className="text-center mb-4">Loading...</div>}

                    {!loading && (
                        <div className="product-grid">
                            {products.length > 0 ? (
                                products.map(product => (
                                    <div key={product.id} className="card mb-4 shadow-sm">
                                        <div className="card-body">
                                            <h5 className="card-title">{product.name}</h5>
                                            <p className="card-text">Price: ${Number.isFinite(Number(product.price)) ? Number(product.price).toFixed(2) : '0.00'}</p>
                                            <div className="d-flex justify-content-between">
                                                <Link to={`/admin/products/${product.id}`} className="btn btn-primary btn-sm">View Details</Link>
                                                {status === 'active' && (
                                                    <Link to={`/admin/products/edit/${product.id}`} className="btn btn-warning btn-sm">Edit</Link>
                                                )}
                                                {status === 'deleted' && (
                                                    <button className="btn btn-success btn-sm" onClick={() => restoreProduct(product.id)}>Restore</button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p>No products found.</p>
                            )}
                        </div>
                    )}

                    {!loading && status === 'active' && (
                        <div className="text-center mt-4">
                            <Link to="/admin/products/new" className="btn btn-success">Create new</Link>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default ProductsList;
