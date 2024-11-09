import axios from 'axios';

const getTotalRevenue = async (startDate, endDate) => {
    try {
        const response = await axios.get('/api/total-revenue', {params: {startDate, endDate}});

        if (response.status === 200) {
            return response.data.totalRevenue;
        } else {
            console.error('Failed to fetch total revenue');
            return null;
        }
    } catch (error) {
        console.error('Error fetching total revenue:', error);
        return null;
    }
};

const getTotalRevenueChart = async (startDate, endDate) => {
    try {
        const response = await axios.get('/api/total-revenue', {
            params: {
                startDate: startDate,
                endDate: endDate,
            },
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching revenue trend data:", error);
        throw error;
    }
};

const getTotalProductsSold = async (startDate, endDate) => {
    try {
        const response = await axios.get('/api/total-products-sold', {params: {startDate, endDate}});

        if (response.status === 200) {
            return response.data.totalProductsSold;
        } else {
            console.error('Failed to fetch total products sold');
            return null;
        }
    } catch (error) {
        console.error('Error fetching total products sold:', error);
        return null;
    }
};

const getTotalProductsSoldChart = async (startDate, endDate) => {
    try {
        const response = await axios.get('/api/total-products-sold-chart', {
            params: {
                startDate: startDate,
                endDate: endDate,
            },
        });
        return response.data.salesTrend;
    } catch (error) {
        console.error("Error fetching product sales trend data:", error);
        throw error;
    }
};

const getRevenueTrend = async (startDate, endDate) => {
    try {
        const response = await axios.get('/api/top-sold-products', {params: {startDate, endDate}});

        if (response.status === 200) {
            return {
                topSoldProducts: response.data.topSoldProducts
            };
        } else {
            console.error('Failed to fetch revenue trend');
            return null;
        }
    } catch (error) {
        console.error('Error fetching revenue trend:', error);
        return null;
    }
};

const getProductSales = async (startDate, endDate) => {
    try {
        const response = await axios.get('/api/product-sales', {params: {startDate, endDate}});

        if (response.status === 200) {
            return {
                totalProductsSold: response.data.totalProductsSold,
                productSales: response.data.productSales
            };
        } else {
            console.error('Failed to fetch product sales');
            return null;
        }
    } catch (error) {
        console.error('Error fetching product sales:', error);
        return null;
    }
};

export {
    getTotalRevenue,
    getTotalRevenueChart,
    getTotalProductsSold,
    getTotalProductsSoldChart,
    getRevenueTrend,
    getProductSales
};
