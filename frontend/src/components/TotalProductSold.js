import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types';
import {getTotalProductsSold, getTotalProductsSoldChart} from '../services/adminDashboardService';
import {Spinner} from "react-bootstrap";
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
} from 'recharts';

const TotalProductsSold = ({startDate, endDate}) => {
    const [totalSold, setTotalSold] = useState(0);
    const [salesTrend, setSalesTrend] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (startDate && endDate) {
            const fetchSalesData = async () => {
                setLoading(true);
                setError(null);
                try {
                    const totalSoldData = await getTotalProductsSold(startDate.toISOString(), endDate.toISOString());
                    setTotalSold(totalSoldData || 0);

                    const salesTrendData = await getTotalProductsSoldChart(startDate.toISOString(), endDate.toISOString());
                    setSalesTrend(salesTrendData || []);
                } catch (err) {
                    setError('Failed to load products sold or sales trend data.');
                    console.error(err);
                } finally {
                    setLoading(false);
                }
            };

            fetchSalesData();
        }
    }, [startDate, endDate]);

    const transformedData = salesTrend.map(data => ({
        date: data.date,
        Sold: data.salesAmount,
    }));

    const salesValues = transformedData.map(item => item.Sold);
    const minSold = salesValues.length ? Math.min(...salesValues) : 0;
    const maxSold = salesValues.length ? Math.max(...salesValues) : 1;

    const yAxisDomain = [
        minSold * 0.8,
        maxSold > minSold * 10
            ? maxSold * 1.1
            : Math.ceil(maxSold * 1.2)
    ];

    return (
        <div>
            <h2>Total Products Sold</h2>
            {loading ? (
                <div className="text-center mt-5">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : error ? (
                <p className="text-danger">{error}</p>
            ) : (
                <div>
                    <p>{totalSold} items</p>
                    <ResponsiveContainer width="100%" height={400}>
                        <LineChart
                            data={transformedData}
                            margin={{
                                top: 5,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}
                        >
                            <CartesianGrid strokeDasharray="3 3"/>
                            <XAxis
                                dataKey="date"
                                tickFormatter={(tick) => new Date(tick).toLocaleDateString()}
                            />
                            <YAxis
                                domain={yAxisDomain}
                                tickFormatter={(value) => value.toLocaleString()}
                                scale={maxSold > minSold * 10 ? "log" : "auto"}
                                allowDataOverflow
                            />
                            <Tooltip formatter={(value) => `${value.toLocaleString()} items`}/>
                            <Line type="monotone" dataKey="Sold" stroke="#8884d8"/>
                        </LineChart>
                    </ResponsiveContainer>
                </div>
            )}
        </div>
    );
};

TotalProductsSold.propTypes = {
    startDate: PropTypes.instanceOf(Date).isRequired,
    endDate: PropTypes.instanceOf(Date).isRequired,
};

export default TotalProductsSold;
