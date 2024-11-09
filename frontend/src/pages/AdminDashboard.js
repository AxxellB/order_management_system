import React, {useState} from 'react';
import DatePicker from 'react-datepicker';
import "react-datepicker/dist/react-datepicker.css";
import '../styles/AdminDashboard.css';

import TotalRevenue from '../components/TotalRevenue';
import TotalProductsSold from '../components/TotalProductSold';
import ProductSales from '../components/ProductSales';
import RevenueTrend from '../components/RevenueTrend';

const getCurrentMonthDates = () => {
    const now = new Date();
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    return {startOfMonth, endOfMonth};
};

function AdminDashboard() {
    const {startOfMonth, endOfMonth} = getCurrentMonthDates();
    const [startDate, setStartDate] = useState(startOfMonth);
    const [endDate, setEndDate] = useState(endOfMonth);
    const [isDatePickerOpen, setIsDatePickerOpen] = useState(false);

    const handleDateChange = (dates) => {
        const [start, end] = dates;
        setStartDate(start);
        setEndDate(end);

        if (start && end) {
            setIsDatePickerOpen(false);
        }
    };

    const toggleDatePicker = () => {
        setIsDatePickerOpen(!isDatePickerOpen);
    };

    return (
        <div className="admin-dashboard">
            <h1>Admin Dashboard</h1>

            <div className="date-range-button-container">
                <button className="date-range-button" onClick={toggleDatePicker}>
                    Select Date Range
                </button>
                {isDatePickerOpen && (
                    <div className="date-picker-container">
                        <DatePicker
                            selected={startDate}
                            onChange={handleDateChange}
                            startDate={startDate}
                            endDate={endDate}
                            selectsRange
                            inline
                            shouldCloseOnSelect={false}
                        />
                    </div>
                )}
            </div>

            <div className="dashboard-summary">
                <div className="summary-card">
                    <TotalRevenue startDate={startDate} endDate={endDate}/>
                </div>
                <div className="summary-card">
                    <TotalProductsSold startDate={startDate} endDate={endDate}/>
                </div>
            </div>

            <div className="dashboard-charts">
                <div className="chart-card">
                    <RevenueTrend startDate={startDate} endDate={endDate}/>
                </div>

                <div className="chart-card">
                    <ProductSales startDate={startDate} endDate={endDate}/>
                </div>
            </div>
        </div>
    );
}

export default AdminDashboard;
