import React, {useEffect, useState} from 'react';
import PropTypes from 'prop-types';
import {getTotalRevenue, getTotalRevenueChart} from '../services/adminDashboardService';
import {Spinner} from "react-bootstrap";
import {ResponsiveContainer, AreaChart, Area, CartesianGrid, XAxis, YAxis, Tooltip} from "recharts";

const TotalRevenue = ({startDate, endDate}) => {
    const [totalRevenue, setTotalRevenue] = useState(0);
    const [totalRevenueChart, setTotalRevenueChart] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (startDate && endDate) {
            const fetchTotalRevenueData = async () => {
                setLoading(true);
                setError(null);
                try {
                    const totalRevenueData = await getTotalRevenue(startDate.toISOString(), endDate.toISOString());
                    setTotalRevenue(totalRevenueData || 0);

                    const trendData = await getTotalRevenueChart(startDate.toISOString(), endDate.toISOString());
                    setTotalRevenueChart(trendData.revenueTrend || []);
                } catch (err) {
                    setError('Failed to load total revenue or trend data.');
                    console.error(err);
                } finally {
                    setLoading(false);
                }
            };

            fetchTotalRevenueData();
        }
    }, [startDate, endDate]);

    const transformedData = totalRevenueChart.map(data => ({
        name: data.date,
        value: data.revenue,
    }));

    const revenueValues = transformedData.map(item => item.value);
    const minRevenue = revenueValues.length ? Math.min(...revenueValues) : 0;
    const maxRevenue = revenueValues.length ? Math.max(...revenueValues) : 1;

    const yAxisDomain = [
        minRevenue * 0.8,
        maxRevenue > minRevenue * 10
            ? maxRevenue * 1.1
            : Math.ceil(maxRevenue * 1.2)
    ];

    return (
        <div>
            <h2>Total Revenue</h2>
            {loading ? (
                <div className="text-center mt-5">
                    <Spinner animation="border" variant="primary"/>
                </div>
            ) : error ? (
                <p className="text-danger">{error}</p>
            ) : (
                <div>
                    <p>${totalRevenue}</p>

                    <div className="revenue-trend-chart">
                        <ResponsiveContainer width="100%" height={400}>
                            <AreaChart
                                data={transformedData}
                                margin={{
                                    top: 10,
                                    right: 30,
                                    left: 0,
                                    bottom: 0,
                                }}
                            >
                                <CartesianGrid strokeDasharray="3 3"/>
                                <XAxis
                                    dataKey="name"
                                    tickFormatter={(tick) => new Date(tick).toLocaleDateString()}
                                />
                                <YAxis
                                    domain={yAxisDomain}
                                    tickFormatter={(value) => `$${value.toLocaleString()}`}
                                    scale="log"
                                    allowDataOverflow
                                />
                                <Tooltip formatter={(value) => `$${value.toFixed(2)}`}/>
                                <Area type="monotone" dataKey="value" stroke="#8884d8" fill="#8884d8"/>
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            )}
        </div>
    );
};

TotalRevenue.propTypes = {
    startDate: PropTypes.instanceOf(Date).isRequired,
    endDate: PropTypes.instanceOf(Date).isRequired,
};

export default TotalRevenue;
