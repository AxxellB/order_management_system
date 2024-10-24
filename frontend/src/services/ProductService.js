import axios from "axios";

const hasAvailableQuantity = async (product, quantity) => {
    const response = await axios.get(`api/products/${product.id}`);
    const productStockQuantity = response.data.stockQuantity;

    if(quantity > productStockQuantity){
        return productStockQuantity;
    }
    return null;
}

export default hasAvailableQuantity;