import React, {useState, useEffect, useCallback} from "react";
import {
    Table,
    Badge,
    Button,
    InputGroup,
    FormControl,
    Pagination,
    Spinner,
} from "react-bootstrap";
import axios from "axios";
import {debounce} from "../components/debounce";
import {Link} from 'react-router-dom';
import {useAlert} from "../provider/AlertProvider";


const InventoryManagement = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalItems, setTotalItems] = useState(0);
    const itemsPerPage = 5;
    const [searchTerm, setSearchTerm] = useState("");
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");
    const {showAlert} = useAlert();

    useEffect(() => {
        fetchProducts(currentPage);
    }, [currentPage, searchTerm]);

    useEffect(() => {
        const script = document.createElement("script");
        script.src = "https://kit.fontawesome.com/71035d1681.js";
        script.crossOrigin = "anonymous";
        script.async = true;
        document.body.appendChild(script);
    }, []);

    const fetchProducts = async (page = 1) => {
        try {
            setLoading(true);
            const queryParams = new URLSearchParams({
                status: "active",
                page: page.toString(),
                itemsPerPage: itemsPerPage.toString(),
                search: searchTerm,
            });

            const response = await axios.get(
                `http://localhost/api/products/list?${queryParams.toString()}`
            );

            setProducts(response.data.products);
            setTotalItems(response.data.totalItems);
            setLoading(false);
        } catch (error) {
            showAlert("Failed to fetch products. Please try again.", 'error');
            setLoading(false);
        }
    };

    const debouncedSearch = useCallback(
        debounce((term) => {
            setSearchTerm(term);
            setCurrentPage(1);
        }, 300),
        []
    );

    const handleSearch = (e) => {
        debouncedSearch(e.target.value);
    };

    const getStatusBadge = (stockQuantity) => {
        if (stockQuantity === 0) {
            return <Badge bg="danger">Out of Stock</Badge>;
        } else if (stockQuantity < 50) {
            return <Badge bg="warning">Low Stock</Badge>;
        } else {
            return <Badge bg="success">In Stock</Badge>;
        }
    };

    const restockProduct = async (id) => {
        const quantity = prompt("Enter the quantity to restock:");

        if (!quantity || isNaN(quantity) || quantity <= 0) {
            showAlert("Invalid quantity entered.", "error");
            return;
        }

        try {
            await axios.patch(`http://localhost/api/products/${id}`, {
                quantity: parseInt(quantity, 10),
            });

            showAlert(`Stock updated successfully for product ID ${id}`, "success");

            setProducts((prevProducts) =>
                prevProducts.map((product) =>
                    product.id === id
                        ? {...product, stockQuantity: product.stockQuantity + parseInt(quantity, 10)}
                        : product
                )
            );
        } catch (error) {
            showAlert("Failed to update stock", 'error');
        }
    };

    const totalPages = Math.ceil(totalItems / itemsPerPage);

    return (
        <div className="container mt-5">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h2>Inventory Management</h2>
                <Link to="/admin/bulk-restock">
                    <Button variant="primary">
                        <i className="fa-solid fa-boxes-stacked"></i>
                    </Button>
                </Link>
            </div>

            {message && <div className="alert alert-success">{message}</div>}
            {error && <div className="alert alert-danger">{error}</div>}

            <InputGroup className="mb-3">
                <FormControl
                    placeholder="Search Products..."
                    aria-label="Search"
                    onChange={handleSearch}
                />
            </InputGroup>

            <div className="product-table">
                {loading ? (
                    <div className="text-center mt-3 mb-3">
                        <Spinner animation="border" variant="primary"/>
                    </div>
                ) : (
                    <>
                        <Table responsive striped bordered hover className="mb-4">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                                <th>Stock History</th>
                            </tr>
                            </thead>
                            <tbody>
                            {products.map((product) => (
                                <tr key={product.id}>
                                    <td>{product.id}</td>
                                    <td>{product.name}</td>
                                    <td>{product.stockQuantity}</td>
                                    <td>{getStatusBadge(product.stockQuantity)}</td>
                                    <td>
                                        <Button
                                            variant="primary"
                                            onClick={() => restockProduct(product.id)}
                                        >
                                            Restock
                                        </Button>
                                    </td>
                                    <td>
                                        <Link to={`/admin/product/${product.id}/stock-history`}>
                                            <Button><i className="fa-solid fa-chart-line"></i></Button>
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </Table>

                        <Pagination className="justify-content-center">
                            {Array.from({length: totalPages}, (_, index) => (
                                <Pagination.Item
                                    key={index + 1}
                                    active={index + 1 === currentPage}
                                    onClick={() => setCurrentPage(index + 1)}
                                >
                                    {index + 1}
                                </Pagination.Item>
                            ))}
                        </Pagination>
                    </>
                )}
            </div>
        </div>
    );
};

export default InventoryManagement;
