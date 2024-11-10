import axios from "axios";
import {useAuth} from "../provider/AuthProvider";

const hasAvailableQuantity = async (productId, requestedQuantity) => {
    const response = await axios.get(`http://localhost/api/products/${productId}`);
    const availableStock = response.data.stockQuantity;
    return requestedQuantity <= availableStock ? null : availableStock;
};

const canAddToBasket = async (productId, quantity) => {
    const response = await axios.get('http://localhost/api/basket');
    const basket = response.data.basket;
    const productInBasket = basket.find(basketItem => basketItem.product.id === productId);

    if (!productInBasket) {
        return hasAvailableQuantity(productId, quantity);
    }

    const totalRequestedQuantity = productInBasket.quantity + quantity;
    const productResponse = await axios.get(`http://localhost/api/products/${productId}`);
    const productStockQuantity = productResponse.data.stockQuantity;

    return totalRequestedQuantity <= productStockQuantity ? null : productStockQuantity;
};

export {hasAvailableQuantity, canAddToBasket};