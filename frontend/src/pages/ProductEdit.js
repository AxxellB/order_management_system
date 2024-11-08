import React, {useState, useEffect} from 'react';
import axios from 'axios';
import {useParams, useNavigate} from 'react-router-dom';
import {useAlert} from "../provider/AlertProvider";

const EditProduct = () => {
    const {id} = useParams();
    const [formData, setFormData] = useState({
        name: '',
        price: '',
        description: '',
        categories: []
    });
    const [availableCategories, setAvailableCategories] = useState([]);
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();
    const {showAlert} = useAlert();

    useEffect(() => {
        fetchProduct();
        fetchCategories();
    }, []);

    const fetchProduct = async () => {
        try {
            const response = await axios.get(`http://localhost/api/products/${id}`);
            setFormData({
                ...response.data,
                categories: response.data.categories.map(category => category.id)
            });
        } catch (err) {
            showAlert("Error fetching product data", "error");
        }
    };

    const fetchCategories = async () => {
        try {
            const response = await axios.get('http://localhost/api/categories/list?filter=true');
            setAvailableCategories(response.data.categories || []);
        } catch {
            showAlert('Error fetching categories', "error");
        }
    };

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleCategoryChange = (e) => {
        const categoryId = parseInt(e.target.value);
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

        const {categories, name, price, description} = formData;
        const preparedData = {
            categories,
            name,
            price,
            description
        };

        try {
            const response = await axios.put(`http://localhost/api/products/${id}`, preparedData);
            showAlert('Product updated successfully', "success");
            navigate('/admin/products');
        } catch (error) {
            if (error.response && error.response.data) {
                showAlert(`Server validation errors:", ${error.response.data.errors}`, "error");
                setErrors(error.response.data.errors || {});
            } else {
                showAlert(`Unexpected error:, ${error}`, "error");
            }
        }
    };

    return (
        <div className="container mt-5">
            <h1>Edit Product: {formData.name}</h1>
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
                    />
                    {errors.price && <div className="text-danger">{errors.price}</div>}
                </div>

                <div className="mb-3">
                    <label className="form-label">Description</label>
                    <textarea
                        name="description"
                        value={formData.description}
                        onChange={handleChange}
                        className="form-control"
                    ></textarea>
                    {errors.description && <div className="text-danger">{errors.description}</div>}
                </div>

                <div className="mb-3">
                    <label className="form-label">Categories</label>
                    <div>
                        {availableCategories.length > 0 ? (
                            availableCategories.map((category) => (
                                <div key={category.id} className="form-check">
                                    <input
                                        type="checkbox"
                                        id={`category-${category.id}`}
                                        value={category.id}
                                        checked={formData.categories.includes(category.id)}
                                        onChange={handleCategoryChange}
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

                <button type="submit" className="btn btn-primary">Save Changes</button>
            </form>
        </div>
    );
};

export default EditProduct;
