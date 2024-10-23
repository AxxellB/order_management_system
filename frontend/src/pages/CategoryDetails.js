import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams } from 'react-router-dom';

const CategoryDetails = () => {
    const { id } = useParams();
    const [category, setCategory] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchCategory = async () => {
            try {
                const response = await axios.get(`http://localhost/api/categories/${id}`);
                setCategory(response.data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchCategory();
    }, [id]);

    if (loading) return <div className="text-center mt-5">Loading...</div>;
    if (error) return <div className="text-center text-danger mt-5">Error: {error}</div>;

    return (
        <div className="container mt-5">
            <h1 className="text-center mb-5">{category.name}</h1>
            <h2>Products in this Category:</h2>
            <ul>
                {category.products.length > 0 ? (
                    category.products.map(product => (
                        <li key={product.id}>{product.name}</li>
                    ))
                ) : (
                    <p>No products in this category</p>
                )}
            </ul>
        </div>
    );
};

export default CategoryDetails;
