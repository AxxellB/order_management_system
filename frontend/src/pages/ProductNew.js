import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { useAlert } from "../provider/AlertProvider";
import styles from '../styles/EditProduct.module.css';

const NewProduct = () => {
    const [formData, setFormData] = useState({
        name: '',
        price: '',
        description: '',
        categories: []
    });
    const [availableCategories, setAvailableCategories] = useState([]);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();
    const { showAlert } = useAlert();

    useEffect(() => {
        const delayFetchCategories = setTimeout(() => {
            fetchCategories();
        }, 100);

        return () => clearTimeout(delayFetchCategories);
    }, []);

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://localhost/api/categories/list?filter=true');
            setAvailableCategories(response.data.categories || []);
        } catch (err) {
            showAlert(`Error fetching categories: ${err}`, "error");
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleCategoryChange = (e) => {
        const categoryId = parseInt(e.target.value, 10);
        setFormData((prevFormData) => {
            const updatedCategories = e.target.checked
                ? [...prevFormData.categories, categoryId]
                : prevFormData.categories.filter((id) => id !== categoryId);
            return {
                ...prevFormData,
                categories: updatedCategories
            };
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        const validationErrors = {};
        if (!formData.name.trim()) validationErrors.name = 'Name is required.';
        if (!formData.price || isNaN(formData.price) || parseFloat(formData.price) <= 0) {
            validationErrors.price = 'Please enter a valid price.';
        }
        if (formData.categories.length === 0) {
            validationErrors.categories = 'Please select at least one category.';
        }

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);
            showAlert('Please check your inputs again.', "error");
            return;
        }

        const preparedData = {
            categories: formData.categories,
            name: formData.name,
            price: formData.price,
            description: formData.description || ''
        };

        try {
            await axios.post('http://localhost/api/products/new', preparedData);
            showAlert('Product created successfully', "success");
            navigate('/admin/products');
        } catch (error) {
            if (error.response && error.response.data) {
                showAlert("Product could not be created! Please try again.", "error");
                setErrors(error.response.data.errors || {});
            } else {
                showAlert("Oops, we encountered an unexpected error!", "error");
            }
        }
    };

    if (loading) {
        return (
            <div className={styles.spinnerContainer}>
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading categories...</span>
                </div>
            </div>
        );
    }

    return (
        <div className={styles.container}>
            <h1 className={styles.title}>Create New Product</h1>
            <button onClick={() => navigate(-1)} className={styles.backButton}>Back</button>
            <form onSubmit={handleSubmit}>
                <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Product Name</label>
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className={styles.formInput}
                    />
                    {errors.name && <div className={styles.errorMessage}>{errors.name}</div>}
                </div>

                <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Price</label>
                    <input
                        type="number"
                        name="price"
                        value={formData.price}
                        onChange={handleChange}
                        className={styles.formInput}
                        step="0.01"
                        min="0.00"
                    />
                    {errors.price && <div className={styles.errorMessage}>{errors.price}</div>}
                </div>

                <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Description</label>
                    <textarea
                        name="description"
                        value={formData.description}
                        onChange={handleChange}
                        className={styles.formTextarea}
                    ></textarea>
                    {errors.description && <div className={styles.errorMessage}>{errors.description}</div>}
                </div>

                <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Categories</label>
                    <div className={styles.categoriesContainer}>
                        {availableCategories.length > 0 ? (
                            availableCategories.map((category) => (
                                <div key={category.id} className={styles.categoryCheckbox}>
                                    <input
                                        type="checkbox"
                                        id={`category-${category.id}`}
                                        value={category.id}
                                        onChange={handleCategoryChange}
                                    />
                                    <label htmlFor={`category-${category.id}`}>{category.name}</label>
                                </div>
                            ))
                        ) : (
                            <p>No categories available</p>
                        )}
                    </div>
                    {errors.categories && <div className={styles.errorMessage}>{errors.categories}</div>}
                </div>

                <button type="submit" className={styles.submitButton}>Create Product</button>
            </form>
        </div>
    );
};

export default NewProduct;
