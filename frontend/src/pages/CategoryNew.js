import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const NewCategory = () => {
    const [formData, setFormData] = useState({
        name: ''
    });
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            [name]: value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.post('http://localhost/api/categories/new', formData);
            alert('Category created successfully');
            navigate('/admin/categories');
        } catch (error) {
            setErrors(error.response.data.errors || {});
        }
    };

    return (
        <div className="container mt-5">
            <h1>Create New Category</h1>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label className="form-label">Category Name</label>
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className="form-control"
                    />
                    {errors.name && <div className="text-danger">{errors.name}</div>}
                </div>
                <button type="submit" className="btn btn-primary">Create Category</button>
            </form>
        </div>
    );
};

export default NewCategory;
