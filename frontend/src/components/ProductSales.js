import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types';
import {getProductSales} from '../services/adminDashboardService';
import {Spinner} from "react-bootstrap";
import {BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer} from 'recharts';

const ProductSales = ({startDate, endDate}) => {
    const [sales, setSales] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (startDate && endDate) {
            const fetchSales = async () => {
                setLoading(true);
                setError(null);
                try {
                    const data = await getProductSales(startDate.toISOString(), endDate.toISOString());
                    setSales(data.productSales || []);
                } catch (err) {
                    setError('Failed to load product sales data.');
                    console.error(err);
                } finally {
                    setLoading(false);
                }
            };

            fetchSales();
        }
    }, [startDate, endDate]);

    const transformedData = sales.map((product) => ({
        name: product.name,
        totalSold: Number(product.totalSold),
    }));

    return (
        <div>
            <h2>Product Sales</h2>
            {loading ? (
                <div className="text-center mt-5">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : error ? (
                <p className="text-danger">{error}</p>
            ) : transformedData.length > 0 ? (
                <ResponsiveContainer width="100%" height={400}>
                    <BarChart
                        data={transformedData}
                        margin={{
                            top: 5,
                            right: 30,
                            left: 20,
                            bottom: 5,
                        }}
                        barSize={20}
                    >
                        <CartesianGrid strokeDasharray="3 3"/>
                        <XAxis dataKey="name" scale="point" padding={{left: 10, right: 10}}/>
                        <YAxis/>
                        <Tooltip/>
                        <Bar dataKey="totalSold" fill="#8884d8" background={{fill: '#eee'}}/>
                    </BarChart>
                </ResponsiveContainer>
            ) : (
                <p>No product sales data available.</p>
            )}
        </div>
    );
};

ProductSales.propTypes = {
    startDate: PropTypes.instanceOf(Date).isRequired,
    endDate: PropTypes.instanceOf(Date).isRequired,
};

export default ProductSales;
