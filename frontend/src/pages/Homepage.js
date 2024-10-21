import React, { useState, useEffect } from 'react';
import '../Homepage.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import { addToBasket } from '../services/basketService';

const ProductsList = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [basket, setBasket] = useState({});

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                const response = await fetch('http://localhost/api/products');

                if (!response.ok) {
                    throw new Error('Failed to fetch products');
                }

                const data = await response.json();
                setProducts(data);
                setLoading(false);
            } catch (error) {
                setError(error.message);
                setLoading(false);
            }
        };

        fetchProducts();
    }, []);

    const handleAddToBasket = (productId, quantity) => {
        addToBasket(productId, quantity)
    };

    if (loading) return <div className="text-center mt-5">Loading...</div>;
    if (error) return <div className="text-center text-danger mt-5">Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-5">Shop Our Products</h1>
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
    );
};

export default ProductsList;
