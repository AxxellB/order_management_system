import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, Link } from 'react-router-dom';
import styles from '../styles/ProductPage.module.css';
import PlaceholderImage from '../assets/imgs/placeholder.jpg';
import { addToBasket } from '../services/basketService';
import { canAddToBasket } from '../services/productService';

const ProductPage = () => {
    const { id } = useParams();
    const [product, setProduct] = useState(null);
    const [recommendations, setRecommendations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchProduct = async () => {
            try {
                const response = await axios.get(`http://localhost/api/products/${id}`);
                setProduct(response.data);
                setLoading(false);
            } catch (error) {
                setError(error.response?.data?.error || 'Error fetching product');
                setLoading(false);
            }
        };
        fetchProduct();
    }, [id]);

    useEffect(() => {
        const fetchRecommendations = async () => {
            try {
                const response = await axios.get('http://localhost/api/products/randomised?limit=5');
                setRecommendations(response.data.products);
            } catch (error) {
                console.error('Error fetching recommendations:', error);
            }
        };
        fetchRecommendations();
    }, [id]);

    const handleAddToBasket = async (productId, quantity) => {
        try {
            const result = await canAddToBasket(productId, quantity);
            if (result === null) {
                await addToBasket(productId, quantity);
                alert('Product added to basket successfully.');
            } else {
                alert(`Insufficient stock quantity. Only ${result} items are available.`);
            }
        } catch (error) {
            if (error.response && error.response.status === 401) {
                alert('You must be logged in to add products to the basket.');
            } else {
                console.error('Error adding to basket:', error);
                alert('An error occurred while adding the product to the basket. Please try again.');
            }
        }
    };

    const getImageUrl = (imageName) => imageName ? `http://localhost/api/file/${imageName}` : PlaceholderImage;

    if (loading) return <div className="text-center mt-5">Loading...</div>;
    if (error) return <div className="text-center text-danger mt-5">{error}</div>;

    return (
        <div className={styles.productPage}>
            <div className={styles.productContainer}>
                <div className={styles.leftSection}>
                    <div className={styles.imageWrapper}>
                        <img
                            src={getImageUrl(product.image)}
                            alt={product.name}
                            className={styles.productImage}
                        />
                    </div>
                </div>

                <div className={styles.middleSection}>
                    <h3 className={styles.descriptionTitle}>Description</h3>
                    <p className={styles.descriptionText}>{product.description}</p>
                </div>

                <div className={styles.rightSection}>
                    <div className={styles.detailsCard}>
                        <h1 className={styles.productTitle}>{product.name}</h1>
                        <p className={styles.productPrice}>${parseFloat(product.price).toFixed(2)}</p>
                        <p className={product.stockQuantity > 0 ? styles.inStock : styles.outOfStock}>
                            {product.stockQuantity > 0 ? 'In Stock' : 'Out of Stock'}
                        </p>
                        <div className={styles.quantityContainer}>
                            <label htmlFor="mainQuantity" className={styles.quantityLabel}>Quantity:</label>
                            <input
                                type="number"
                                id="mainQuantity"
                                min="1"
                                defaultValue="1"
                                className={styles.quantityInput}
                            />
                        </div>
                        <button
                            className={styles.addToBasketButton}
                            onClick={() => {
                                const quantity = parseInt(document.getElementById("mainQuantity").value, 10);
                                handleAddToBasket(product.id, quantity);
                            }}
                        >
                            Add to Basket
                        </button>
                    </div>
                </div>
            </div>

            <div className={styles.recommendationsSection}>
                <h2 className={styles.recommendationsTitle}>You may also like</h2>
                <div className={styles.recommendationsGrid}>
                    {recommendations.map(recProduct => (
                        <div key={recProduct.id} className="card mb-4 shadow-sm">
                            <div className={styles.imageContainer}>
                                <Link to={`/product/${recProduct.id}`} >
                                    <img
                                        src={getImageUrl(recProduct.image)}
                                        alt={recProduct.name}
                                        className={styles.recommendationImage}
                                    />
                                </Link>
                            </div>
                            <div className="card-body">
                                <Link to={`/product/${recProduct.id}`} className={styles.productTitleLink}>
                                    <h5 className="card-title">{recProduct.name}</h5>
                                </Link>
                                <p className="card-price">Price: ${Number(recProduct.price).toFixed(2)}</p>

                                <div className="d-flex justify-content-end align-items-center">
                                    <input
                                        type="number"
                                        className="quantity-input"
                                        min="1"
                                        defaultValue="1"
                                        id={`quantity-${recProduct.id}`}
                                    />
                                    <button
                                        className="btn btn-success ms-2"
                                        onClick={() => {
                                            const quantity = parseInt(document.getElementById(`quantity-${recProduct.id}`).value, 10);
                                            if (quantity > 0) {
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
    );
};

export default ProductPage;
