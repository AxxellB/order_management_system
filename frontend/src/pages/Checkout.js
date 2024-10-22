import React, { useEffect, useState } from 'react';
import axios from 'axios';
import {useNavigate} from "react-router-dom";
import '../styles/Checkout.css';

const Checkout = () => {
    const [basket, setBasket] = useState([]);
    const [addresses, setAddresses] = useState([]);
    const [selectedAddress, setSelectedAddress] = useState('');
    const [basketLoading, setBasketLoading] = useState(true);
    const [addressLoading, setAddressLoading] = useState(true);
    const [cardDetails, setCardDetails] = useState({
        cardNumber: '',
        cardExpiry: '',
        cardCVC: ''
    });
    const [totalPrice, setTotalPrice] = useState(0);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchBasketItems = async () => {
            setBasketLoading(true);
            try {
                const response = await axios.get('/api/basket');
                setBasket(response.data.basket);
            } catch (error) {
                console.error('Error fetching basket items', error);
            } finally {
                setBasketLoading(false);
            }
        };
        fetchBasketItems();
    }, []);

    useEffect(() => {
        const total = basket.reduce(
            (acc, item) => acc + item.product.price * item.quantity,
            0
        );
        setTotalPrice(parseFloat(total.toFixed(2)));
    }, [basket]);

    useEffect(() => {
        const fetchAddresses = async () => {
            setAddressLoading(true);
            try {
                const response = await axios.get('/api/addresses');
                setAddresses(response.data.addresses);
            } catch (error) {
                console.error('Error fetching addresses', error);
            } finally {
                setAddressLoading(false);
            }
        };
        fetchAddresses();
    }, []);

    const handleCardChange = (e) => {
        const { name, value } = e.target;
        setCardDetails(prevDetails => ({
            ...prevDetails,
            [name]: value
        }));
    };

    const handleCheckout = async () => {
        try {
            const checkoutData = {
                addressId: selectedAddress
            };

            await axios.post('/api/orders', checkoutData);
            alert('Checkout successful');
            navigate('/')
        } catch (error) {
            console.error('Error during checkout', error);
            alert('Checkout failed');
        }
    };

    return (
        <div className="checkout-page mt-4 mb-4">
            <h2 className="checkout-title">Checkout</h2>

            {basketLoading ? (
                <div className="loading">Loading basket items...</div>
            ) : basket.length === 0 ? (
                <div className="empty-basket">Your basket is empty</div>
            ) : (
                <div className="basket-items">
                    <h3>Your Basket</h3>
                    {basket.map(item => (
                        <div key={item.id} className="basket-item">
                            <p className="product-name">Product: {item.product.name}</p>
                            <p className="product-details">Quantity: {item.quantity} | Price: ${item.product.price}</p>
                        </div>
                    ))}
                    <h3 className="total-price">Total Price: ${totalPrice.toFixed(2)}</h3>
                </div>
            )}

            <div className="address-selection">
                <h3>Select Address</h3>
                {addressLoading ? (
                    <div className="loading">Loading addresses...</div>
                ) : (
                    <select
                        value={selectedAddress}
                        onChange={(e) => setSelectedAddress(e.target.value)}
                        className="address-dropdown"
                    >
                        <option value="">Choose an address</option>
                        {addresses.map(address => (
                            <option key={address.id} value={address.id}>
                                {address.line2
                                    ? `${address.line}, ${address.line2}, ${address.city}, ${address.country}, ${address.postcode}`
                                    : `${address.line}, ${address.city}, ${address.country}, ${address.postcode}`
                                }
                            </option>
                        ))}
                    </select>
                )}
            </div>

            <div className="card-details">
                <h3>Debit Card Information</h3>
                <input
                    type="text"
                    name="cardNumber"
                    placeholder="Card Number"
                    value={cardDetails.cardNumber}
                    onChange={handleCardChange}
                    className="card-input"
                />
                <input
                    type="text"
                    name="cardExpiry"
                    placeholder="Expiry Date (MM/YY)"
                    value={cardDetails.cardExpiry}
                    onChange={handleCardChange}
                    className="card-input"
                />
                <input
                    type="text"
                    name="cardCVC"
                    placeholder="CVC"
                    value={cardDetails.cardCVC}
                    onChange={handleCardChange}
                    className="card-input"
                />
            </div>

            <button onClick={handleCheckout} className="checkout-button">Complete Checkout</button>
        </div>
    );
};

export default Checkout;
