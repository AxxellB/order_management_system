import axios from "axios";

const addToBasket = async (productId, quantity) => {
    const payload = {
        productId: productId,
        quantity: quantity
    }
    try{
        await axios.post('/api/basket', payload)
        alert("Product was added to basket")
    }catch (error){
        alert("Product could not be added to basket")
    }
}
const updateQuantity = async (productId, newQuantity) => {
    try {
        const response = await axios.put(`/api/basket/${productId}`, {
            quantity: newQuantity,
        });

        if (response.status === 200) {
            console.log('Quantity updated successfully');
        } else {
            console.log('Failed to update quantity');
        }
    }catch (error){
        console.log(error)
    }
}
const removeProduct = async (productId) => {
    try{
        const response = await axios.delete(`api/basket/${productId}`)
        if (response.status === 200) {
            alert('Product removed successfully');
        } else {
            alert('Failed to remove product');
        }
    }catch (error){
        console.log(error);
    }
}
const clearBasket = async () =>{
    try{
        const response = await axios.delete('api/basket')
        if (response.status === 200) {
            alert('Basket cleared successfully');
        } else {
            alert('Failed to clear basket');
        }
    }catch (error){
        console.log(error);
    }
}

export { addToBasket, clearBasket, removeProduct, updateQuantity };