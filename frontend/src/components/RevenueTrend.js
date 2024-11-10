import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types';
import {getRevenueTrend} from '../services/adminDashboardService';
import {Spinner} from "react-bootstrap";
import {BarChart, Bar, Cell, XAxis, YAxis, CartesianGrid, ResponsiveContainer} from 'recharts';

const colors = ['#0088FE', '#BD113AFF', '#FFBB28'];

const getPath = (x, y, width, height) => {
    return `M${x},${y + height}C${x + width / 3},${y + height} ${x + width / 2},${y + height / 3}
  ${x + width / 2}, ${y}
  C${x + width / 2},${y + height / 3} ${x + (2 * width) / 3},${y + height} ${x + width}, ${y + height}
  Z`;
};

const TriangleBar = (props) => {
    const {fill, x, y, width, height} = props;
    return <path d={getPath(x, y, width, height)} stroke="none" fill={fill}/>;
};

const RevenueTrend = ({startDate, endDate}) => {
    const [revenueTrend, setRevenueTrend] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (startDate && endDate) {
            const fetchRevenueTrend = async () => {
                setLoading(true);
                setError(null);
                try {
                    const data = await getRevenueTrend(startDate.toISOString(), endDate.toISOString());
                    setRevenueTrend(data.topSoldProducts || []);
                } catch (err) {
                    setError('Failed to load revenue trend.');
                    console.error(err);
                } finally {
                    setLoading(false);
                }
            };

            fetchRevenueTrend();
        }
    }, [startDate, endDate]);

    const formattedData = revenueTrend.map(item => ({
        name: item.name,
        value: parseInt(item.totalSold, 10)
    }));

    const revenueValues = formattedData.map(item => item.value);
    const minRevenue = revenueValues.length ? Math.min(...revenueValues) : 0;
    const maxRevenue = revenueValues.length ? Math.max(...revenueValues) : 1;

    const yAxisDomain = [
        Math.floor(minRevenue * 0.8),
        maxRevenue > minRevenue * 10
            ? Math.ceil(maxRevenue * 1.1)
            : Math.ceil(maxRevenue * 1.2)
    ];

    return (
        <div>
            <h2>Top Sold Products</h2>
            {loading ? (
                <div className="text-center mt-5">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : error ? (
                <p className="text-danger">{error}</p>
            ) : formattedData.length > 0 ? (
                <ResponsiveContainer width="100%" height={400}>
                    <BarChart
                        data={formattedData}
                        margin={{
                            top: 20,
                            right: 30,
                            left: 20,
                            bottom: 5,
                        }}
                    >
                        <CartesianGrid strokeDasharray="3 3"/>
                        <XAxis dataKey="name"/>
                        <YAxis
                            domain={yAxisDomain}
                            tickFormatter={(value) => value.toLocaleString()}
                            allowDataOverflow
                        />
                        <Bar dataKey="value" shape={<TriangleBar/>} label={{position: 'top'}}>
                            {formattedData.map((entry, index) => (
                                <Cell key={`cell-${index}`} fill={colors[index % colors.length]}/>
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            ) : (
                <p>No top sold products data available.</p>
            )}
        </div>
    );
};

RevenueTrend.propTypes = {
    startDate: PropTypes.instanceOf(Date).isRequired,
    endDate: PropTypes.instanceOf(Date).isRequired,
};

export default RevenueTrend;
