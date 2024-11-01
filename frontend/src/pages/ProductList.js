import React, { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { debounce } from "../components/debounce";

const ProductsList = () => {
    const navigate = useNavigate();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [status, setStatus] = useState('active');
    const [categories, setCategories] = useState([]);
    const [searchTerm, setSearchTerm] = useState("");
    const [sortOption, setSortOption] = useState("name_asc");

    const [filters, setFilters] = useState({
        category: '',
        minPrice: '',
        maxPrice: ''
    });

    useEffect(() => {
        fetchProducts();
        fetchCategories();
    }, [status, searchTerm, sortOption]);

    const fetchProducts = async () => {
        try {
            setLoading(true);
            const [sortField, sortOrder] = sortOption.split('_');
            const queryParams = new URLSearchParams({
                status,
                search: searchTerm,
                sort: sortField,
                order: sortOrder,
            });

            if (filters.category) queryParams.append('category', filters.category);
            if (filters.minPrice) queryParams.append('minPrice', filters.minPrice);
            if (filters.maxPrice) queryParams.append('maxPrice', filters.maxPrice);

            const response = await axios.get(`http://localhost/api/products/list?${queryParams.toString()}`);

            setProducts(Array.isArray(response.data.products) ? response.data.products : []);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://localhost/api/categories/list?filter=true');
            setCategories(response.data.categories);
        } catch (error) {
            console.error('Error fetching categories:', error);
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

    const debouncedSearch = useCallback(
        debounce((term) => {
            setSearchTerm(term);
        }, 300),
        []
    );

    const handleSearch = (e) => {
        debouncedSearch(e.target.value);
    };

    const handleSortChange = (e) => {
        setSortOption(e.target.value);
    };

    if (error) return <div>Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-4">Product Management</h1>
            <div className="row">
                <div className="col-md-2">
                    <div className="card shadow-sm p-3 mb-4 filter-card">
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
                                className="form-control small-placeholder"
                                placeholder="Enter min price"
                                min="0"
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Max Price</label>
                            <input
                                type="number"
                                name="maxPrice"
                                value={filters.maxPrice}
                                onChange={handleFilterChange}
                                className="form-control small-placeholder"
                                placeholder="Enter max price"
                                min="0"
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

                    <div className="mb-4 d-flex align-items-center justify-content-between">
                        <input
                            type="text"
                            placeholder="Search products..."
                            onChange={handleSearch}
                            className="form-control me-2"
                            style={{ width: '550px' }}
                        />

                        <div className="d-flex align-items-center">
                            <label className="me-2">Sort By:</label>
                            <select
                                value={sortOption}
                                onChange={handleSortChange}
                                className="form-select"
                                style={{ width: '200px' }}
                            >
                                <option value="name_asc">Name (A-Z)</option>
                                <option value="name_desc">Name (Z-A)</option>
                                <option value="price_asc">Price (Low to High)</option>
                                <option value="price_desc">Price (High to Low)</option>
                            </select>
                        </div>
                    </div>

                    {loading && <div className="text-center mb-4">Loading...</div>}

                    {!loading && (
                        <div className="product-grid">
                            {products.length > 0 ? (
                                products.map(product => (
                                    <div key={product.id} className="card mb-4 shadow-sm">
                                        <div className="card-body">
                                            <h5 className="card-title">{product.name}</h5>
                                            <p className="card-text">Price:
                                                ${Number.isFinite(Number(product.price)) ? Number(product.price).toFixed(2) : '0.00'}</p>
                                            <div className="card-actions">
                                                <Link to={`/admin/products/${product.id}`}
                                                      className="btn btn-primary btn-sm">View Details</Link>
                                                {status === 'active' && (
                                                    <Link to={`/admin/products/edit/${product.id}`}
                                                          className="btn btn-warning btn-sm">Edit</Link>
                                                )}
                                                {status === 'deleted' && (
                                                    <button className="btn btn-success btn-sm"
                                                            onClick={() => restoreProduct(product.id)}>Restore</button>
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
