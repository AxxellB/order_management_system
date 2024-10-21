import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../styles/Homepage.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import { addToBasket } from '../services/basketService';

const Homepage = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const [basket, setBasket] = useState({});

    const [categories, setCategories] = useState([]);
    const [filters, setFilters] = useState({
        category: '',
        minPrice: '',
        maxPrice: ''
    });

    useEffect(() => {
        fetchCategories();
        fetchProducts();
    }, []);

    const fetchProducts = async () => {
        try {
            setLoading(true);

            const queryParams = new URLSearchParams({
                status: 'active'
            });

            if (filters.category) queryParams.append('category', filters.category);
            if (filters.minPrice) queryParams.append('minPrice', filters.minPrice);
            if (filters.maxPrice) queryParams.append('maxPrice', filters.maxPrice);

            const response = await axios.get(`http://localhost/api/products/list?${queryParams.toString()}`);
            setProducts(response.data);
            setLoading(false);
        } catch (error) {
            setError(error.message);
            setLoading(false);
        }
    };

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://localhost/api/categories/');
            setCategories(response.data);
        } catch (error) {
            console.error('Error fetching categories:', error);
        }
    };

    const handleAddToBasket = (productId, quantity) => {
        addToBasket(productId, quantity)
    };

    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters({
            ...filters,
            [name]: value
        });
    };

    const applyFilters = () => {
        fetchProducts();
    };

    if (loading) return <div className="text-center mt-5">Loading...</div>;
    if (error) return <div className="text-center text-danger mt-5">Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-5">Shop Our Products</h1>
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
                                <option value="">All Categories</option>
                                {categories.length > 0 ? (
                                    categories.map((category) => (
                                        <option key={category.id} value={category.id}>
                                            {category.name}
                                        </option>
                                    ))
                                ) : (
                                    <option disabled>No categories available</option>
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

                        <button className="btn btn-primary w-100" onClick={applyFilters}>
                            Apply Filters
                        </button>
                    </div>
                </div>

                <div className="col-md-10">
                    <div className="product-grid">
                        {products.map(product => (
                            <div key={product.id} className="card mb-4 shadow-sm">
                                <div className="card-body">
                                    <h5 className="card-title">{product.name}</h5>
                                    <p className="card-text">Price: ${Number(product.price).toFixed(2)}</p>

                                    <div className="d-flex justify-content-end align-items-center">
                                        <input
                                            type="number"
                                            className="quantity-input"
                                            min="1"
                                            max={product.stockQuantity}
                                            defaultValue="1"
                                            id={`quantity-${product.id}`}
                                        />
                                        <button
                                            className="btn btn-success ms-2"
                                            onClick={() => {
                                                const quantity = parseInt(document.getElementById(`quantity-${product.id}`).value, 10);
                                                if (quantity > 0 && quantity <= product.stockQuantity) {
                                                    handleAddToBasket(product.id, quantity);
                                                } else {
                                                    alert('Invalid quantity');
                                                }
                                            }}
                                        >
                                            <i className="bi bi-cart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Homepage;
