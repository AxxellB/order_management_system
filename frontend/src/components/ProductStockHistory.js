import React, {useState, useEffect} from 'react';
import axios from 'axios';
import {useParams, Link, useNavigate} from 'react-router-dom';
import {Spinner} from 'react-bootstrap';
import {LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer} from 'recharts';

const ProductStockHistory = () => {
    const {id} = useParams();
    const navigate = useNavigate();
    const [stockHistory, setStockHistory] = useState([]);
    const [productName, setProductName] = useState("");
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [opacity, setOpacity] = useState({stock: 1});

    useEffect(() => {
        if (id) {
            fetchStockHistory();
        } else {
            setError("Product ID is missing.");
        }
    }, [id]);

    const fetchStockHistory = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get(`/api/products/${id}/stock-history`);

            const historyData = response?.data?.stockHistory;
            if (!Array.isArray(historyData)) {
                throw new Error("Unexpected data format: stock history is not an array.");
            }

            const formattedStockHistory = historyData
                .filter(item => item.timestamp && typeof item.stock === 'number')
                .map(item => ({
                    timestamp: item.timestamp,
                    stock: item.stock
                }));

            if (formattedStockHistory.length === 0) {
                throw new Error("No valid stock history records found.");
            }

            setStockHistory(formattedStockHistory);

            setProductName(historyData[0].product || "Product");

            setLoading(false);
        } catch (err) {
            setError(err.message || 'Failed to fetch stock history.');
            setLoading(false);
        }
    };

    const handleMouseEnter = (o) => {
        const {dataKey} = o;
        setOpacity((prevState) => ({...prevState, [dataKey]: 0.5}));
    };

    const handleMouseLeave = (o) => {
        const {dataKey} = o;
        setOpacity((prevState) => ({...prevState, [dataKey]: 1}));
    };

    const stockValues = stockHistory.map(item => item.stock);
    const minStock = stockValues.length ? Math.min(...stockValues) : 0;
    const maxStock = stockValues.length ? Math.max(...stockValues) : 0;
    const yAxisDomain = [Math.floor(minStock * 0.8), Math.ceil(maxStock * 1.2)];

    return (
        <div className="container mt-5">
            <h2 className="text-center mb-4">Stock History</h2>

            {productName && <h3 className="text-center mb-4">{productName}</h3>}

            {loading ? (
                <div className="text-center mt-5">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : error ? (
                <p className="text-danger text-center">{error}</p>
            ) : stockHistory.length > 0 ? (
                <>
                    <ResponsiveContainer width="100%" height={400}>
                        <LineChart
                            data={stockHistory}
                            margin={{
                                top: 10,
                                right: 30,
                                left: 0,
                                bottom: 0,
                            }}
                        >
                            <CartesianGrid strokeDasharray="3 3"/>
                            <XAxis dataKey="timestamp"
                                   tickFormatter={(timestamp) => new Date(timestamp).toLocaleDateString()}/>
                            <YAxis domain={yAxisDomain}/>
                            <Tooltip/>
                            <Legend onMouseEnter={handleMouseEnter} onMouseLeave={handleMouseLeave}/>
                            <Line
                                type="monotone"
                                dataKey="stock"
                                strokeOpacity={opacity.stock}
                                stroke="#8884d8"
                                activeDot={{r: 8}}
                            />
                        </LineChart>
                    </ResponsiveContainer>
                    <Link to="/admin/inventory" className="btn btn-link mt-3">
                        <button className="btn btn-secondary mt-3">Back to inventory</button>
                    </Link>
                </>
            ) : (
                <p className="text-center">No stock history available for this product.</p>
            )}
        </div>
    );
};

export default ProductStockHistory;