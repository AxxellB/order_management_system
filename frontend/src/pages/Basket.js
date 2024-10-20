import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from "axios";
import {useAuth} from "../provider/AuthProvider";

const Basket = ({ updateQuantity, removeProduct, clearBasket }) => {
    const [basket, setBasket] = useState([]);
    const [totalPrice, setTotalPrice] = useState(0);
    const navigate = useNavigate();
    const token = useAuth();

    useEffect(() => {
        const fetchBasket = async () => {
                try {
                    const response = await axios.get('/api/basket', {
                        headers: {
                            Authorization: `Bearer ${token.token}`,
                        },
                    });
                    setBasket(response.data.basket);
                    console.log(response.data)
                    const total = basket.reduce(
                        (acc, item) => acc + item.product.price * item.quantity,
                        0
                    );
                    setTotalPrice(total);
                } catch (error) {
                    console.error('Error fetching basket:', error);
                    if (error.response && error.response.status === 401) {
                    }
                }
            };

        fetchBasket();
    }, [navigate]);

    const handleQuantityChange = (e, productId) => {
        updateQuantity(productId, parseInt(e.target.value, 10));
    };

    const handleRemoveProduct = (productId) => {
        removeProduct(productId);
    };

    const handleClearBasket = () => {
        clearBasket();
    };

    const handleCheckout = () => {
        navigate('/order/create');
    };

    return (
        <div className="basket-container">
            <h1>Your Basket</h1>

            {basket && basket.length > 0 ? (
                <>
                    {basket.map((item) => (
                        <div className="basket-item" key={item.product.id}>
                            <div className="product-details">
                                <strong>{item.product.name}</strong>
                                <br />
                                Price: {item.product.price}$
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
                                        style={{ width: '50px' }}
                                    />
                                    <button type="submit" className="btn">
                                        Edit
                                    </button>
                                </form>

                                <button
                                    className="btn btn-danger"
                                    onClick={() => handleRemoveProduct(item.product.id)}
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    ))}

                    <div className="clear-basket-section">
                        <button className="btn btn-danger" onClick={handleClearBasket}>
                            Clear Basket
                        </button>
                    </div>

                    <div className="total-box">
                        <h3>Total Price: {totalPrice}$</h3>
                        <button className="checkout-button" onClick={handleCheckout}>
                            Checkout
                        </button>
                    </div>
                </>
            ) : (
                <p>Your basket is empty.</p>
            )}
        </div>
    );
};

export default Basket;
