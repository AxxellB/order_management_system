import React, { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { debounce } from "../components/debounce";
import PlaceholderImage from "../assets/imgs/placeholder.jpg";
import { useAlert } from "../provider/AlertProvider";
import styles from '../styles/Homepage.module.css';

const ProductsList = () => {
    const navigate = useNavigate();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [status, setStatus] = useState('active');
    const [categories, setCategories] = useState([]);
    const [searchTerm, setSearchTerm] = useState("");
    const [sortOption, setSortOption] = useState("name_asc");
    const fileInputRefs = useRef({});

    const [filters, setFilters] = useState({
        category: '',
        minPrice: '',
        maxPrice: ''
    });

    const [currentPage, setCurrentPage] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const itemsPerPage = 10;
    const { showAlert } = useAlert();

    useEffect(() => {
        const delayFetchCategories = setTimeout(() => {
            fetchCategories();
        }, 50);

        const delayFetchProducts = setTimeout(() => {
            fetchProducts();
        }, 100);

        return () => {
            clearTimeout(delayFetchCategories);
            clearTimeout(delayFetchProducts);
        };
    }, [currentPage, searchTerm, sortOption]);

    const fetchProducts = async () => {
        try {
            setLoading(true);
            const [sortField, sortOrder] = sortOption.split('_');
            const queryParams = new URLSearchParams({
                status,
                page: currentPage.toString(),
                itemsPerPage: itemsPerPage.toString(),
                search: searchTerm,
                sort: sortField,
                order: sortOrder,
            });

            if (filters.category) queryParams.append('category', filters.category);
            if (filters.minPrice) queryParams.append('minPrice', filters.minPrice);
            if (filters.maxPrice) queryParams.append('maxPrice', filters.maxPrice);

            const response = await axios.get(`http://localhost/api/products/list?${queryParams.toString()}`);
            setProducts(Array.isArray(response.data.products) ? response.data.products : []);
            setTotalItems(response.data.totalItems || 0);
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
        const { name, value } = e.target;
        setFilters({ ...filters, [name]: value });
    };

    const applyFilters = () => {
        setCurrentPage(1);
        fetchProducts();
    };

    const restoreProduct = async (id) => {
        const confirmRestore = window.confirm('Are you sure you want to restore this product?');
        if (confirmRestore) {
            try {
                await axios.delete(`http://localhost/api/products/${id}`, {
                    data: { action: 'restore' },
                });
                showAlert('Product restored successfully', "success");
                fetchProducts();
            } catch (error) {
                showAlert(`Error restoring product ${error}`, "error");
            }
        }
    };

    const handleStatusChange = (newStatus) => {
        if (status !== newStatus) setStatus(newStatus);
    };

    const handleSortChange = (e) => {
        setSortOption(e.target.value);
    };

    const handleFileChange = (productId, event) => {
        const file = event.target.files[0];
        if (file) {
            handleImageUpload(productId, file);
        }
    };

    const handleImageUpload = async (productId, file) => {
        if (!file) {
            showAlert("No file selected", "error");
            return;
        }
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post(`http://localhost/api/products/${productId}/upload-image`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (response.status === 201 || response.status === 200) {
                showAlert('Image uploaded successfully', "success");
                fetchProducts();
            } else {
                showAlert('Failed to upload image', "error");
            }
        } catch (error) {
            showAlert('An error occurred while uploading the image', "success");
        }
    };

    const getImageUrl = (imageName) => {
        return imageName ? `http://localhost/api/file/${imageName}` : PlaceholderImage;
    };

    const totalPages = Math.ceil(totalItems / itemsPerPage);

    return (
        <div className={`${styles.container} mt-5`}>
            <h1 className={`${styles.textCenter} mb-4`}>Product Management</h1>
            <div className="row">
                <div className="col-md-2">
                    <div className={`${styles.card} shadow-sm p-3 mb-4 ${styles.filterCard}`}>
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
                                {categories.map((category) => (
                                    <option key={category.id} value={category.id}>
                                        {category.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Min Price</label>
                            <input
                                type="number"
                                name="minPrice"
                                value={filters.minPrice}
                                onChange={handleFilterChange}
                                className={`form-control ${styles.smallPlaceholder}`}
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
                                className={`form-control ${styles.smallPlaceholder}`}
                                placeholder="Enter max price"
                                min="0"
                            />
                        </div>
                        <button className={`btn btn-primary w-100 ${styles.gradientButton}`} onClick={applyFilters}>
                            Apply Filters
                        </button>

                    </div>

                    <div className="text-center mt-3">
                        <Link to="/admin/products/new" className="btn btn-success w-75 ">Create new</Link>
                    </div>
                </div>

                <div className="col-md-10">
                    <div className="text-center mb-4">
                        <button onClick={() => handleStatusChange('active')}
                                className={`btn ${status === 'active' ? 'btn-primary' : 'btn-secondary'}`}>
                            Active Products
                        </button>
                        <button onClick={() => handleStatusChange('deleted')}
                                className={`btn ${status === 'deleted' ? 'btn-primary' : 'btn-secondary'}`}>
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

                    {loading ? (
                        <div className={`${styles.message} ${styles.loadingMessage}`}>Loading...</div>
                    ) : error ? (
                        <div className={`${styles.message} ${styles.errorMessage}`}>Error: {error}</div>
                    ) : products.length === 0 ? (
                        <div className={`${styles.message} ${styles.noProductsMessage}`}>
                            No products matched your search criteria.
                        </div>
                    ) : (
                        <div className={styles.productGrid}>
                            {products.map((product) => (
                                <div key={product.id} className={`${styles.card} mb-4 shadow-sm`}>
                                    <div className={styles.imageContainer}>
                                        <img
                                            src={getImageUrl(product.image)}
                                            alt={product.name}
                                            className={styles.productImage}
                                        />
                                        <div className={styles.editOverlay}>
                                            <label className={styles.editButton}>
                                                Edit
                                                <input
                                                    type="file"
                                                    ref={(el) => (fileInputRefs.current[product.id] = el)}
                                                    onChange={(e) => handleFileChange(product.id, e)}
                                                    style={{ display: 'none' }}
                                                />
                                            </label>
                                        </div>
                                    </div>
                                    <div className={styles.cardBody}>
                                        <h5 className={styles.cardTitle}>{product.name}</h5>
                                        <p className={styles.cardText}>Price: ${Number(product.price).toFixed(2)}</p>
                                        <div className={styles.cardActions}>
                                            <Link to={`/admin/products/${product.id}`} className="btn btn-primary btn-sm px-1">View Details</Link>
                                            {status === 'active' && (
                                                <Link to={`/admin/products/edit/${product.id}`} className="btn btn-warning btn-sm px-1">Edit</Link>
                                            )}
                                            {status === 'deleted' && (
                                                <button className="btn btn-success btn-sm px-1" onClick={() => restoreProduct(product.id)}>
                                                    Restore
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
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

export default ProductsList;
