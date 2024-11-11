import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, Link } from 'react-router-dom';
import styles from '../styles/ProductPage.module.css';
import PlaceholderImage from '../assets/imgs/placeholder.jpg';
import { addToBasket } from '../services/basketService';
import { canAddToBasket } from '../services/productService';
import { useAlert } from "../provider/AlertProvider";
import { useAuth } from "../provider/AuthProvider";

const ProductPage = () => {
    const { id } = useParams();
    const [product, setProduct] = useState(null);
    const [recommendations, setRecommendations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const { showAlert } = useAlert();
    const { token } = useAuth();

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

        const delayFetchProduct = setTimeout(  ()=> {fetchProduct() }, 50);
        return () => { clearTimeout(delayFetchProduct); }
    }, [id]);

    useEffect(() => {
        const fetchRecommendations = async () => {
            try {
                const response = await axios.get('http://localhost/api/products/randomised?limit=5');
                setRecommendations(response.data.products);
            } catch (error) {
                showAlert("Error fetching recommendations. Please try again.", "error");

            }
        };

        const delayFetchRecomendation = setTimeout(  ()=> {fetchRecommendations() }, 100);
        return () => { clearTimeout(delayFetchRecomendation); }
    }, [id]);

    const handleAddToBasket = async (productId, productName, quantity) => {
        if (!token) {
            showAlert("You must be logged in to add products to the basket.", "error");
            return;
        }

        if (!Number.isInteger(quantity) || quantity <= 0) {
            showAlert("Please enter a valid quantity greater than zero.", "error");
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
            showAlert("An error occurred while adding the product to the basket. Please try again.", "error");
        }
    };

    const getImageUrl = (imageName) => imageName ? `http://localhost/api/file/${imageName}` : PlaceholderImage;

    if (loading) return (
        <div className="text-center mt-5">
            <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Loading...</span>
            </div>
        </div>
    );

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

                    {product.categories && product.categories.length > 0 && (
                        <div className={styles.categoriesSection}>
                            <h4 className={styles.categoriesTitle}>Categories</h4>
                            <ul className={styles.categoryList}>
                                {product.categories.map((category) => (
                                    <li key={category.id} className={styles.categoryItem}>
                                        {category.name}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
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
                                handleAddToBasket(product.id, product.name, quantity);
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
                        <div key={recProduct.id} className={`${styles.card} mb-4 shadow-sm`}>
                            <div className={styles.imageContainer}>
                                <Link to={`/product/${recProduct.id}`} >
                                    <img
                                        src={getImageUrl(recProduct.image)}
                                        alt={recProduct.name}
                                        className={styles.recommendationImage}
                                    />
                                </Link>
                            </div>
                            <div className={styles.cardBody}>
                                <Link to={`/product/${recProduct.id}`} className={styles.productTitleLink}>
                                    <h5 className={styles.cardTitle}>{recProduct.name}</h5>
                                </Link>
                                <p className={styles.cardPrice}>Price: ${Number(recProduct.price).toFixed(2)}</p>

                                <div className="d-flex justify-content-end align-items-center">
                                    {product.stockQuantity > 0 ? (
                                        <button
                                            className="btn btn-success"
                                            onClick={() => handleAddToBasket(product.id, product.name, 1)}
                                        >
                                            <i className="bi bi-cart"></i>
                                        </button>
                                    ) : (
                                        <button className="btn btn-danger" disabled>
                                            Out of Stock
                                        </button>
                                    )}
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
