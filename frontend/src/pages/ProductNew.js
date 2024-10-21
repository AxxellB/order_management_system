import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const NewProduct = () => {
    const [formData, setFormData] = useState({
        name: '',
        price: '',
        description: '',
        stockQuantity: '',
        categories: []
    });
    const [categories, setCategories] = useState([]);
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();

    useEffect(() => {
        fetchCategories();
    }, []);

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://localhost/api/categories/');
            setCategories(response.data);
        } catch (err) {
            console.error('Error fetching categories:', err);
        }
    };

    const handleChange = (e) => {
        const { name, value, type } = e.target;
        if (type === 'checkbox') {
            const categoryId = parseInt(e.target.value, 10);
            setFormData((prevState) => ({
                ...prevState,
                categories: prevState.categories.includes(categoryId)
                    ? prevState.categories.filter((id) => id !== categoryId)
                    : [...prevState.categories, categoryId]
            }));
        } else {
            setFormData({
                ...formData,
                [name]: value
            });
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.post('http://localhost/api/products/new', formData);
            alert('Product created successfully');
            navigate('/admin/products');
        } catch (error) {
            setErrors(error.response.data.errors || {});
        }
    };

    return (
        <div className="container mt-5">
            <h1>Create New Product</h1>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label className="form-label">Product Name</label>

                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className="form-control"
                    />

                    {errors.name && <div className="text-danger">{errors.name}</div>}
                </div>

                <div className="mb-3">
                    <label className="form-label">Price</label>

                    <input
                        type="number"
                        name="price"
                        value={formData.price}
                        onChange={handleChange}
                        className="form-control"
                        step="0.01"
                        min="0.00"
                        max="99999999.99"
                    />
                    {errors.price && <div className="text-danger">{errors.price}</div>}
                </div>

                <div className="mb-3">
                    <label className="form-label">Description</label>

                    <textarea
                        name="description"
                        value={formData.description}
                        onChange={handleChange}
                        className="form-control">
                    </textarea>

                    {errors.description && <div className="text-danger">{errors.description}</div>}
                </div>

                <div className="mb-3">
                    <label className="form-label">Stock Quantity</label>

                    <input
                        type="number"
                        name="stockQuantity"
                        value={formData.stockQuantity}
                        onChange={handleChange}
                        className="form-control"
                    />

                    {errors.stockQuantity && <div className="text-danger">{errors.stockQuantity}</div>}
                </div>

                <div className="mb-3">
                    <label className="form-label">Categories</label>
                    <div>
                        {categories.length > 0 ? (
                            categories.map((category) => (
                                <div key={category.id} className="form-check form-check-inline">

                                    <input
                                        type="checkbox"
                                        id={`category-${category.id}`}
                                        value={category.id}
                                        onChange={handleChange}
                                        className="form-check-input"
                                    />

                                    <label htmlFor={`category-${category.id}`} className="form-check-label">
                                        {category.name}
                                    </label>
                                </div>
                            ))
                        ) : (
                            <p>No categories available</p>
                        )}
                    </div>
                    {errors.categories && <div className="text-danger">{errors.categories}</div>}
                </div>

                <button type="submit" className="btn btn-primary">Create Product</button>
            </form>
        </div>
    );
};

export default NewProduct;
