import React, {useState} from "react";
import {Button, Table, Form, Alert, Image, Row, Col} from "react-bootstrap";
import axios from "axios";
import {ExampleCsvFormat} from "../assets/assets";
import { useAlert } from "../provider/AlertProvider";

const BulkProductRestock = () => {
    const [step, setStep] = useState(1);
    const [csvData, setCsvData] = useState([]);
    const [confirmedChanges, setConfirmedChanges] = useState([]);
    const [error, setError] = useState("");
    const [confirmAll, setConfirmAll] = useState(false);
    const { showAlert } = useAlert();

    const handleCsvUpload = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = async (event) => {
                const lines = event.target.result.split("\n").slice(1);
                const data = lines.map((line) => {
                    const [productId, stockAmount] = line.split(",");
                    if (!productId || !stockAmount) return null;
                    return {
                        productId: productId.trim(),
                        stockAmount: parseInt(stockAmount.trim(), 10),
                    };
                }).filter(Boolean);

                try {
                    const response = await axios.post("/api/products/validate-csv", {products: data});
                    setCsvData(response.data.validatedProducts);
                    setStep(2);
                } catch (error) {
                    showAlert("Failed to validate products. Please check the CSV format.", "error");
                    setTimeout(() => setError(""), 5000);
                }
            };
            reader.readAsText(file);
        }
    };

    const handleConfirmationChange = (index) => {
        setCsvData((prevData) =>
            prevData.map((item, idx) => (idx === index ? {...item, confirmed: !item.confirmed} : item))
        );
    };

    const handleConfirmAllChange = () => {
        setConfirmAll(!confirmAll);
        setCsvData((prevData) =>
            prevData.map((item) =>
                item.isValid ? {...item, confirmed: !confirmAll} : item
            )
        );
    };

    const submitChanges = async () => {
        const confirmedItems = csvData.filter((item) => item.confirmed);
        try {
            await axios.post("/api/products/bulk-restock", {changes: confirmedItems});
            setConfirmedChanges(confirmedItems);
            setStep(3);
        } catch (error) {
            showAlert("Failed to apply stock changes. Make sure you have confirmed the stock changes", "error");
            setTimeout(() => setError(""), 5000);
        }
    };

    return (
        <div className="container mt-5">

            {error && <Alert variant="danger">{error}</Alert>}

            {step === 1 && (
                <>
                    <Row className="mb-4">
                        <Col md={6}>

                            <h2>Bulk Restock</h2>

                            <p>
                                To bulk restock products, upload a CSV file in the format: <strong>Product ID, Stock
                                Amount</strong>.
                                Each row should contain a product's unique ID and the quantity to add to stock.
                            </p>
                            <p>
                                Example:
                                <br/>
                                <code>101, 25</code> - Restocks product ID 101 by 25 units
                                <br/>
                                <code>102, 10</code> - Restocks product ID 102 by 10 units
                            </p>
                            <br/>
                            <Form.Group>
                                <Form.Control type="file" accept=".csv" onChange={handleCsvUpload}/>
                            </Form.Group>
                        </Col>
                        <Col md={6} className="d-flex justify-content-center align-items-center">
                            <Image src={ExampleCsvFormat} alt="Example CSV format" className="my-3" fluid/>
                        </Col>
                    </Row>
                </>
            )}

            {step === 2 && (
                <>
                    <h3>Confirm Stock Changes</h3>
                    <Table striped bordered hover>
                        <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Stock Amount to Add</th>
                            <th>New Stock</th>
                            <th>Confirmation</th>
                            <th>Error</th>
                        </tr>
                        </thead>
                        <tbody>
                        {Array.isArray(csvData) && csvData.map((product, index) => (
                            <tr key={product.productId}>
                                <td>{product.productId}</td>
                                <td>{product.name}</td>
                                <td>{product.currentStock}</td>
                                <td>{product.stockAmount}</td>
                                <td>{product.isValid ? product.currentStock + product.stockAmount : 'N/A'}</td>
                                <td>
                                    {product.isValid ? (
                                        <Form.Check
                                            type="checkbox"
                                            checked={product.confirmed || false}
                                            onChange={() => handleConfirmationChange(index)}
                                        />
                                    ) : (
                                        <span className="text-danger">Not allowed</span>
                                    )}
                                </td>
                                <td>
                                    {!product.isValid && (
                                        <span className="text-danger">{product.error}</span>
                                    )}
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </Table>
                    <div className="d-flex justify-content-end mb-2">
                        <Form.Check
                            type="checkbox"
                            label="Confirm All"
                            checked={confirmAll}
                            onChange={handleConfirmAllChange}
                        />
                    </div>
                    <Button variant="primary" onClick={submitChanges}>Submit Changes</Button>
                    <Button variant="secondary" onClick={() => setStep(1)}>Cancel</Button>
                </>
            )}

            {step === 3 && (
                <>
                    <h3>Changes Applied Successfully</h3>
                    <Table striped bordered hover>
                        <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Stock Added</th>
                            <th>New Stock Level</th>
                        </tr>
                        </thead>
                        <tbody>
                        {confirmedChanges.map((product) => (
                            <tr key={product.productId}>
                                <td>{product.productId}</td>
                                <td>{product.name}</td>
                                <td>{product.stockAmount}</td>
                                <td>{product.currentStock + product.stockAmount}</td>
                            </tr>
                        ))}
                        </tbody>
                    </Table>
                    <Button variant="primary" onClick={() => setStep(1)}>New Bulk Update</Button>
                </>
            )}
        </div>
    );
};

export default BulkProductRestock;
