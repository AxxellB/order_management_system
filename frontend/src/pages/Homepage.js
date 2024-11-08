import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import '../styles/Homepage.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import {addToBasket} from '../services/basketService';
import {canAddToBasket} from "../services/productService";
import {debounce} from "../components/debounce";
import PlaceholderImage from "../assets/imgs/placeholder.jpg";
import {useAlert} from "../provider/AlertProvider";
import {useAuth} from "../provider/AuthProvider";
import styles from "../styles/ProductPage.module.css";

const Homepage = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [categories, setCategories] = useState([]);

    const [filters, setFilters] = useState({
        category: '',
        minPrice: '',
        maxPrice: ''
    });

    const [currentPage, setCurrentPage] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const itemsPerPage = 10;

    const [searchTerm, setSearchTerm] = useState("");
    const [sortOption, setSortOption] = useState("name_asc");

    const {showAlert} = useAlert();
    const {token} = useAuth();

    useEffect(() => {
        fetchCategories();
        fetchProducts();
    }, [currentPage, searchTerm, sortOption]);

    const fetchProducts = async () => {
        try {
            setLoading(true);
            const [sortField, sortOrder] = sortOption.split('_');
            const queryParams = new URLSearchParams({
                status: 'active',
                page: currentPage.toString(),
                itemsPerPage: itemsPerPage.toString(),
                search: searchTerm,
                sort: sortField,
                order: sortOrder
            });
            if (filters.category) queryParams.append('category', filters.category);
            if (filters.minPrice) queryParams.append('minPrice', filters.minPrice);
            if (filters.maxPrice) queryParams.append('maxPrice', filters.maxPrice);

            const response = await axios.get(`http://localhost/api/products/list?${queryParams.toString()}`);
            setProducts(Array.isArray(response.data.products) ? response.data.products : []);
            setTotalItems(response.data.totalItems || 0);
            setLoading(false);
        } catch (error) {
            setError(error.message);
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


    const debouncedSearch = useCallback(
        debounce((term) => {
            setSearchTerm(term);
            setCurrentPage(1);
        }, 300),
        []
    );

    const handleSearch = (e) => {
        debouncedSearch(e.target.value);
    };

    const handleFilterChange = (e) => {
        const {name, value} = e.target;
        setFilters({
            ...filters,
            [name]: value
        });
    };

    const applyFilters = () => {
        setCurrentPage(1);
        fetchProducts();
    };

    const handleAddToBasket = async (productId, productName, quantity) => {
        if (!token) {
            showAlert("You must be logged in to add products to the basket.", "error");
            return;
        }

        try {
            const stockResult = await canAddToBasket(productId, quantity);
            if (stockResult !== null) {
                showAlert(`Insufficient stock quantity. Only ${stockResult} ${productName} available.`, "error");
                return;
            }

            await addToBasket(productId, productName, quantity, showAlert);
        } catch (error) {
            console.error('Error adding to basket:', error);
            showAlert("An error occurred while adding the product to the basket. Please try again.", "error");
        }
    };
    const getImageUrl = (imageName) => {
        return imageName ? `http://localhost/api/file/${imageName}` : PlaceholderImage;
    };


    const totalPages = Math.ceil(totalItems / itemsPerPage);

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-5">Our Selection of Products</h1>
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
                    <div className="mb-4 d-flex align-items-center justify-content-between">
                        <input
                            type="text"
                            placeholder="Search products..."
                            onChange={handleSearch}
                            className="form-control me-2"
                            style={{width: '550px'}}
                        />

                        <div className="d-flex align-items-center">

                            <label className="me-2">Sort By:</label>
                            <select
                                value={sortOption}
                                onChange={(e) => setSortOption(e.target.value)}
                                className="form-select"
                                style={{width: '200px'}}
                            >
                                <option value="name_asc">Name (A-Z)</option>
                                <option value="name_desc">Name (Z-A)</option>
                                <option value="price_asc">Price (Low to High)</option>
                                <option value="price_desc">Price (High to Low)</option>
                            </select>
                        </div>
                    </div>

                    {loading ? (
                        <div className="text-center mt-5">Loading...</div>
                    ) : error ? (
                        <div className="text-center text-danger mt-5">Error: {error}</div>
                    ) : (
                        <div className="product-grid">
                            {Array.isArray(products) && products.length > 0 ? (
                                products.map(product => (
                                    <div key={product.id} className="card mb-4 shadow-sm">
                                        <div className="image-container">
                                            <Link to={`/product/${product.id}`}>
                                                <img
                                                    src={getImageUrl(product.image)}
                                                    alt={product.name}
                                                    className="product-image"
                                                />
                                            </Link>
                                        </div>
                                        <div className="card-body">
                                            <Link to={`/product/${product.id}`} className="homepage-title-link">
                                                <h5 className="card-title" id={product.name}>{product.name}</h5>
                                            </Link>
                                            <p className="card-price">Price: ${Number(product.price).toFixed(2)}</p>

                                            <div className="d-flex justify-content-end align-items-center">
                                                {product.stockQuantity > 0 ? (
                                                    <>
                                                        <input
                                                            type="number"
                                                            className="quantity-input"
                                                            min="1"
                                                            defaultValue="1"
                                                            id={`quantity-${product.id}`}
                                                        />
                                                        <button
                                                            className="btn btn-success ms-2"
                                                            onClick={() => {
                                                                const quantity = parseInt(document.getElementById(`quantity-${product.id}`).value, 10);
                                                                if (quantity > 0) {
                                                                    handleAddToBasket(product.id, product.name, quantity);
                                                                } else {
                                                                    alert('Invalid quantity');
                                                                }
                                                            }}
                                                        >
                                                            <i className="bi bi-cart"></i>
                                                        </button>
                                                    </>
                                                ) : (
                                                    <button className="btn btn-secondary ms-2" disabled>
                                                        Out of Stock
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div>No products found.</div>
                            )}
                        </div>
                    )}

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
                </div>
            </div>
        </div>
    );
};

export default Homepage;
