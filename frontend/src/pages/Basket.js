import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from "axios";
import { clearBasket, removeProduct, updateQuantity } from "../services/basketService";
import '../styles/Basket.css';

const Basket = () => {
    const [basket, setBasket] = useState([]);
    const [totalPrice, setTotalPrice] = useState(0);
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchBasket = async () => {
            try {
                const response = await axios.get('/api/basket');
                setBasket(response.data.basket);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching basket:', error);
            }
        };

        fetchBasket();
    }, [navigate]);

    useEffect(() => {
        const total = basket.reduce(
            (acc, item) => acc + item.product.price * item.quantity,
            0
        );
        setTotalPrice(parseFloat(total.toFixed(2)));
    }, [basket]);

    const handleQuantityChange = async (e, productId) => {
        const newQuantity = parseInt(e.target.value, 10);

        await updateQuantity(productId, newQuantity);

        setBasket((prevBasket) => {
            return prevBasket.map((item) => {
                if (item.product.id === productId) {
                    return { ...item, quantity: newQuantity };
                }
                return item;
            });
        });
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

                                    <form
                                        onSubmit={(e) => {
                                            e.preventDefault();
                                            handleQuantityChange(e, item.product.id);
                                        }}
                                    >
                                        <input
                                            type="number"
                                            name="quantity"
                                            value={item.quantity}
                                            min="1"
                                            onChange={(e) => handleQuantityChange(e, item.product.id)}
                                            className="quantity-input"
                                        />
                                    </form>

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
