import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, useNavigate } from 'react-router-dom';
import { useAlert } from "../provider/AlertProvider";
import styles from '../styles/NewCategory.module.css';

const EditCategory = () => {
    const { id } = useParams();
    const [formData, setFormData] = useState({ name: '' });
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();
    const { showAlert } = useAlert();

    useEffect(() => {
        const fetchCategory = async () => {
            try {
                const response = await axios.get(`http://localhost/api/categories/${id}`);
                setFormData({ name: response.data.name });
            } catch (err) {
                showAlert(`Error fetching category: ${err}`, "error");
            }
        };

        if (id) {
            fetchCategory();
        }
    }, [id, showAlert]);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.put(`http://localhost/api/categories/${id}`, formData, {
                headers: { 'Content-Type': 'application/json' }
            });
            showAlert('Category updated successfully', "success");
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
            <h1 className={styles.title}>Edit Category</h1>
            <form onSubmit={handleSubmit} className={styles.form}>
                <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Category Name</label>
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        className={`${styles.formInput} ${errors.name ? styles.isInvalid : ''}`}
                        placeholder="Enter category name"
                    />
                    {errors.name && <div className={styles.errorMessage}>{errors.name}</div>}
                </div>
                <div className={styles.buttonGroup}>
                    <button
                        type="button"
                        className={`btn ${styles.button} ${styles.backButton}`}
                        onClick={() => navigate('/admin/categories')}
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        className={`btn ${styles.button} ${styles.createButton}`}
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    );
};

export default EditCategory;
