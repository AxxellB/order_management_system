import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from "axios";
import { clearBasket, removeProduct, updateQuantity } from "../services/basketService";
import hasAvailableQuantity from '../services/ProductService';
import '../styles/Basket.css';

const Basket = () => {
    const [basket, setBasket] = useState([]);
    const [totalPrice, setTotalPrice] = useState(0);
    const [loading, setLoading] = useState(true);

    const navigate = useNavigate();

    useEffect(() => {
        const fetchBasket = async () => {
            try {
                const response = await axios.get('/api/basket');
                const basketItems = await Promise.all(response.data.basket.map(async (item) => {
                    const stockQuantity = await hasAvailableQuantity(item.product, item.quantity);
                    if (stockQuantity !== null) {
                        await updateQuantity(item.product.id, stockQuantity);
                        return { ...item, quantity: stockQuantity, stockWarning: true };
                    }
                    return { ...item, stockWarning: false };
                }));
                setBasket(basketItems);
            } catch (error) {
                console.error('Error fetching basket:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchBasket();
    }, []);

    useEffect(() => {
        const total = basket.reduce(
            (acc, item) => acc + item.product.price * item.quantity,
            0
        );
        setTotalPrice(parseFloat(total.toFixed(2)));
    }, [basket]);

    const handleQuantityChange = async (e, productId) => {
        const inputQuantity = e.target.value;
        const newQuantity = parseInt(inputQuantity, 10);

        if (isNaN(newQuantity) || newQuantity < 1) {
            return;
        }

        const product = basket.find(item => item.product.id === productId).product;
        const stockQuantity = await hasAvailableQuantity(product, newQuantity);

        if (stockQuantity !== null) {
            await updateQuantity(productId, stockQuantity);
            alert(`Only ${stockQuantity} units are available for ${product.name}.`);
            setBasket((prevBasket) => {
                return prevBasket.map((item) => {
                    if (item.product.id === productId) {
                        return { ...item, quantity: stockQuantity, stockWarning: true };
                    }
                    return item;
                });
            });
        } else {
            await updateQuantity(productId, newQuantity);
            setBasket((prevBasket) => {
                return prevBasket.map((item) => {
                    if (item.product.id === productId) {
                        return { ...item, quantity: newQuantity, stockWarning: false };
                    }
                    return item;
                });
            });
        }
    };

    const handleRemoveProduct = async (productId) => {
        await removeProduct(productId);

        setBasket((prevBasket) => {
            const updatedBasket = prevBasket.filter(item => item.product.id !== productId);
            const newTotalPrice = updatedBasket.reduce(
                (acc, item) => acc + item.product.price * item.quantity,
                0
            );
            setTotalPrice(newTotalPrice);
            return updatedBasket;
        });
    };

    const handleClearBasket = async () => {
        await clearBasket();
        setBasket([]);
        setTotalPrice(0);
    };

    const handleCheckout = () => {
        navigate('/checkout');
    };

    return (
        <div className="basket-container mt-4 mb-4">
            <h1 className="basket-title">Your Basket</h1>

            {loading ? (
                <div className="loading">Loading...</div>
            ) : (
                basket && basket.length > 0 ? (
                    <>
                        {basket.map((item) => (
                            <div className="basket-item" key={item.product.id}>
                                <div className="product-details">
                                    <strong>{item.product.name}</strong>
                                    <br />
                                    Price: ${item.product.price}
                                </div>

                                <div className="quantity-controls">
                                    <span>Quantity: {item.quantity}</span>
                                    <input
                                        type="number"
                                        name="quantity"
                                        value={item.quantity}
                                        min="1"
                                        onChange={(e) => handleQuantityChange(e, item.product.id)}
                                        className="quantity-input"
                                    />
                                    {item.stockWarning && (
                                        <p className="text-danger">
                                            Only {item.quantity} left in stock.
                                        </p>
                                    )}
                                    <button
                                        className="btn-remove"
                                        onClick={() => handleRemoveProduct(item.product.id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        ))}

                        <div className="clear-basket-section">
                            <button className="btn-clear" onClick={handleClearBasket}>
                                Clear Basket
                            </button>
                        </div>

                        <div className="total-box">
                            <h3>Total Price: ${totalPrice}</h3>
                            <button className="checkout-button" onClick={handleCheckout}>
                                Checkout
                            </button>
                        </div>
                    </>
                ) : (
                    <p className="empty-basket">Your basket is empty.</p>
                )
            )}
        </div>
    );
};

export default Basket;
