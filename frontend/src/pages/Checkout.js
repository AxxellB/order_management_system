import React, {useEffect, useState} from 'react';
import axios from 'axios';
import {useNavigate} from "react-router-dom";
import '../styles/Checkout.css';
import {hasAvailableQuantity} from "../services/productService";
import {useAlert} from "../provider/AlertProvider";

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
    const [preventCheckout, setPreventCheckout] = useState(false);
    const [basketErrors, setBasketErrors] = useState([]);
    const [discountCode, setDiscountCode] = useState({});
    const [successMessage, setSuccessMessage] = useState('');
    const [errorMessage, setErrorMessage] = useState('');
    const navigate = useNavigate();
    const {showAlert} = useAlert();

    useEffect(() => {
        const fetchBasketItems = async () => {
            try {
                setBasketLoading(true);
                const response = await axios.get('/api/basket');
                if (response.data.basket) {
                    setBasket(response.data.basket);
                }
            } catch (error) {
                console.error('Error fetching basket items', error);
                showAlert("An error occurred! Please try again!", "error");
            } finally {
                setBasketLoading(false);
            }
        };
        fetchBasketItems();
    }, []);

    useEffect(() => {
        const getTotalPrice = () => {
            let total = basket.reduce(
                (acc, item) => acc + item.product.price * item.quantity,
                0
            );
            if (discountCode && discountCode.percentOff) {
                total -= (total * discountCode.percentOff) / 100;
            }

            setTotalPrice(parseFloat(total.toFixed(2)));
        };

        getTotalPrice();
    }, [basket, discountCode]);

    useEffect(() => {
        const fetchAddresses = async () => {
            setAddressLoading(true);
            try {
                const response = await axios.get('/api/addresses');
                if (response.data.addresses && typeof response.data.addresses === 'object') {
                    setAddresses(Object.values(response.data.addresses));
                }
            } catch (error) {
                console.error('Error fetching addresses', error);
                showAlert("An error occurred! Please try again!", "error");
                setAddresses([]);
            } finally {
                setAddressLoading(false);
            }
        };

        fetchAddresses();
    }, []);

    const validateCode = async () => {
        const code = document.getElementById('discount-input').value;
        setSuccessMessage('');
        setErrorMessage('');
        if (code && code.length > 0) {
            try {
                const response = await axios.post('/api/validate-discount-code', {
                    discountCode: code
                });
                setDiscountCode(response.data);
                setSuccessMessage("Code applied successfully!");
            } catch (error) {
                setDiscountCode({});
                setErrorMessage(error.response.data.message || "Failed to apply code.");
            }
        }
    };

    const handleCardChange = (e) => {
        const {name, value} = e.target;
        setCardDetails(prevDetails => ({
            ...prevDetails,
            [name]: value
        }));
    };

    const handleCheckout = async () => {
        setBasketErrors([]);
        let errors = [];

        for (const item of basket) {
            const availableQuantity = await hasAvailableQuantity(item.product.id, item.quantity);
            if (availableQuantity !== null) {
                errors.push({
                    productId: item.product.id,
                    productName: item.product.name,
                    availableQuantity,
                    userQuantity: item.quantity
                });
            }
        }

        if (errors.length > 0) {
            setBasketErrors(errors);
            showAlert('Some products exceed available stock. Please adjust the quantities.', 'error');
            setPreventCheckout(true);
            return;
        }

        try {
            let checkoutData = {}
            if (discountCode && discountCode.percentOff && discountCode.discountCode) {
                checkoutData = {
                    addressId: selectedAddress,
                    cardDetails,
                    discountCode: discountCode.discountCode,
                    percentOff: discountCode.percentOff
                };
            } else {
                checkoutData = {
                    addressId: selectedAddress,
                    cardDetails
                }
            }

            await axios.post('/api/orders', checkoutData);
            showAlert('Order placed successfully!', "success");
            navigate('/order-confirmation');
        } catch (error) {
            console.error('Error during checkout', error);
            showAlert('An error occurred! Please try again', "error");
        }
    };

    if (basketLoading) {
        return <div className="loading">Loading checkout page...</div>;
    }

    if (basket.length === 0) {
        return <div className="empty-basket">Your basket is empty. Add some products and come back!</div>;
    }

    return (
        <div className="checkout-page mt-4 mb-4">
            <h2 className="checkout-title">Checkout</h2>

            <div className="basket-items">
                <h3>Your Basket</h3>
                {basket.map(item => {
                    const error = basketErrors.find(err => err.productId === item.product.id);

                    return (
                        <div key={item.id} className="basket-item">
                            <p className="product-name">Product: {item.product.name}</p>
                            <p className="product-details">
                                Quantity: {item.quantity} | Price: ${item.product.price}
                            </p>
                            {error && (
                                <p className="stock-error" style={{color: 'red'}}>
                                    Only {error.availableQuantity} units available. You've
                                    requested {error.userQuantity}.
                                </p>
                            )}
                        </div>
                    );
                })}

                <div className="discount-section"
                     style={{display: 'flex', flexDirection: 'column', alignItems: 'end'}}>
                    <span>Have a discount code?</span>
                    <div style={{display: 'flex', gap: '10px', marginTop: '5px'}}>
                        <input
                            type="text"
                            className="discount-input"
                            id="discount-input"
                            placeholder="Enter code"
                        />
                        <button className="discount-button" onClick={validateCode}>Apply Code</button>
                    </div>
                    <div id="messageContainer" style={{marginTop: '10px'}}>
                        {successMessage && <p className="success-message" style={{color: 'green'}}>{successMessage}</p>}
                        {errorMessage && <p className="error-message" style={{color: 'red'}}>{errorMessage}</p>}
                    </div>
                </div>


                <h3 className="total-price">Total Price: ${totalPrice.toFixed(2)}</h3>
            </div>

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
                                    : `${address.line}, ${address.city}, ${address.country}, ${address.postcode}`}
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

            <button onClick={handleCheckout} disabled={preventCheckout} className="checkout-button">
                Complete Checkout
            </button>
        </div>
    );
};

export default Checkout;
