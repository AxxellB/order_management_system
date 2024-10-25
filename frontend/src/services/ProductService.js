import axios from "axios";

const hasAvailableQuantity = async (product, quantity) => {
    const response = await axios.get(`api/products/${product.id}`);
    const productStockQuantity = response.data.stockQuantity;

    if(quantity > productStockQuantity){
        return productStockQuantity;
    }
    return null;
}

const canAddToBasket = async(product, quantity) => {
    const response = await axios.get('api/basket');

}

export default hasAvailableQuantity;