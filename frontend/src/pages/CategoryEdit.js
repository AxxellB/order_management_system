import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, useNavigate } from 'react-router-dom';

const EditCategory = () => {
    const { id } = useParams();
    const [formData, setFormData] = useState({
        name: ''
    });
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();

    useEffect(() => {
        const fetchCategory = async () => {
            try {
                const response = await axios.get(`http://localhost/api/categories/${id}`);

                setFormData({ name: response.data.name });
            } catch (err) {
                console.error('Error fetching category:', err);
            }
        };

        if (id) {
            fetchCategory();
        }
    }, [id]);

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.put(`http://localhost/api/categories/${id}`, formData, {
                headers: { 'Content-Type': 'application/json' }
            });
            alert('Category updated successfully');
            navigate('/admin/categories');
        } catch (error) {
            if (error.response && error.response.data.errors) {
                setErrors(error.response.data.errors);
            } else {
                console.error('Error updating category:', error);
            }
        }
    };

    return (
        <div className="container mt-5">
            <h1>Edit Category</h1>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label className="form-label">Category Name</label>
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                    />
                    {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                </div>
                <button type="submit" className="btn btn-primary">Save Changes</button>
            </form>
        </div>
    );
};

export default EditCategory;
