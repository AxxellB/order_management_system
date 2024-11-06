import axios from "axios";

const hasAvailableQuantity = async (productId, requestedQuantity) => {
    const response = await axios.get(`/api/products/${productId}`);
    const availableStock = response.data.stockQuantity;

    return requestedQuantity <= availableStock ? null : availableStock;
};

const canAddToBasket = async (productId, quantity) => {
    try{
        const response = await axios.get('api/basket');
        const basket = response.data.basket;
        const productInBasket = basket.find(basketItem => basketItem.product.id === productId);

        if (!productInBasket) {
            return hasAvailableQuantity(productId, quantity);
        }

        const totalRequestedQuantity = productInBasket.quantity + quantity;
        const productResponse = await axios.get(`api/products/${productId}`);
        const productStockQuantity = productResponse.data.stockQuantity;

        return totalRequestedQuantity <= productStockQuantity ? null : productStockQuantity;
    }catch (error){
        console.log(error);
    }

};

export { hasAvailableQuantity, canAddToBasket };