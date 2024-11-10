import axios from "axios";

const addToBasket = async (productId, productName, quantity, showAlert) => {
    const payload = {productId, quantity};
    await axios.post('http://localhost/api/basket', payload);
    showAlert(`Added ${quantity} ${productName} to basket`, "success");
};

const updateQuantity = async (productId, newQuantity) => {
    await axios.put(`http://localhost/api/basket/${productId}`, {
        quantity: newQuantity,
    });
}
const removeProduct = async (productId) => {
    await axios.delete(`http://localhost/api/basket/${productId}`)
}
const clearBasket = async () => {
    await axios.delete('http://localhost/api/basket')
}

export {addToBasket, clearBasket, removeProduct, updateQuantity};