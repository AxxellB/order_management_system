import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { useAlert } from "../provider/AlertProvider";
import styles from '../styles/NewCategory.module.css';

const NewCategory = () => {
    const [formData, setFormData] = useState({ name: '' });
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();
    const { showAlert } = useAlert();

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.post('http://localhost/api/categories/new', formData);
            showAlert('Category created successfully', "success");
            navigate('/admin/categories');
        } catch (error) {
            if (error.response && error.response.data.errors) {
                setErrors(error.response.data.errors);
            } else {
                showAlert(`Error updating category: ${error}`, "error");
            }
        }
    };

    return (
        <div className={styles.container}>
            <h1 className={styles.title}>Create New Category</h1>
            <form onSubmit={handleSubmit} className={styles.form}>
                <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Category Name</label>
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className={styles.formInput}
                        placeholder="Enter category name"
                    />
                    {errors.name && <div className={styles.errorMessage}>{errors.name}</div>}
                </div>
                <div className={styles.buttonGroup}>
                    <button type="button" className={`btn ${styles.button} ${styles.backButton}`} onClick={() => navigate('/admin/categories')}>
                        Back
                    </button>
                    <button type="submit" className={`btn ${styles.button} ${styles.createButton}`}>Create Category</button>
                </div>
            </form>
        </div>
    );
};

export default NewCategory;
