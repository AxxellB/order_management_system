import React, {useEffect, useState} from 'react';
import {getAddresses, addAddress, editAddress, deleteAddress} from '../services/addressService';
import {useAlert} from "../provider/AlertProvider";
import {useAuth} from "../provider/AuthProvider";

const AddressTab = () => {
    const [addresses, setAddresses] = useState([]);
    const [newAddress, setNewAddress] = useState({
        line: '',
        line2: '',
        city: '',
        country: '',
        postcode: ''
    });
    const [editAddressData, setEditAddressData] = useState(null);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [showEditForm, setShowEditForm] = useState(false);
    const [loading, setLoading] = useState(false);
    const {showAlert} = useAlert();
    const {token} = useAuth();

    useEffect(() => {
        setLoading(true);
        const fetchAddresses = async () => {
            try {
                const fetchedAddresses = await getAddresses();
                setAddresses(fetchedAddresses);
            } catch (error) {
                showAlert('Failed to load addresses', "error");
            } finally {
                setLoading(false);
            }
        };

        if (token) {
            fetchAddresses();
        }
    }, [token]);

    const handleAddAddress = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await addAddress(newAddress);
            setAddresses([...addresses, newAddress]);
            setNewAddress({line: '', line2: '', city: '', country: '', postcode: ''});
            showAlert('Address added successfully', "success");
            setError(null);
            setShowForm(false);
        } catch {
            showAlert('Failed to add address', "error");
        } finally {
            setLoading(false);
        }
    };

    const handleEditAddress = async (e) => {
        e.preventDefault();
        setLoading(true);
        const {id, ...addressData} = editAddressData;
        try {
            await editAddress(id, addressData);
            setAddresses(addresses.map(address =>
                address.id === editAddressData.id ? editAddressData : address
            ));
            setEditAddressData(null);
            showAlert('Address updated successfully', "success");
            setError(null);
            setShowEditForm(false);
        } catch {
            showAlert('Failed to update address', "error");
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteAddress = async (id) => {
        setLoading(true);
        try {
            await deleteAddress(id);
            setAddresses(addresses.filter(address => address.id !== id));
            showAlert('Address deleted successfully', "success");
        } catch {
            showAlert('Failed to delete address', "error");
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        setNewAddress({
            ...newAddress,
            [e.target.name]: e.target.value
        });
    };

    const handleEditInputChange = (e) => {
        setEditAddressData({
            ...editAddressData,
            [e.target.name]: e.target.value
        });
    };

    const openEditForm = (address) => {
        setEditAddressData(address);
        setShowEditForm(true);
    };

    return (
        <div className="container mt-4">

            {loading && (
                <div className="d-flex justify-content-center">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Loading...</span>
                    </div>
                </div>
            )}

            {addresses && addresses.length > 0 && (
                <h2 className="mb-4">My Addresses</h2>
            )}

            <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                {addresses.length > 0 ? (
                    addresses.map(address => (
                        <div key={address.id} className="col">
                            <div className="card h-100">
                                <div className="card-body">
                                    <p>{address.line}</p>
                                    {address.line2 && <p>{address.line2}</p>}
                                    <p>{address.city}, {address.country} - {address.postcode}</p>
                                </div>
                                <div className="card-footer">
                                    <button
                                        className="btn btn-primary me-2"
                                        onClick={() => openEditForm(address)}
                                    >
                                        Edit
                                    </button>
                                    <button
                                        className="btn btn-danger"
                                        onClick={() => handleDeleteAddress(address.id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))
                ) : <></>}
            </div>

            {!loading && !addresses && (
                <p>You don't have any addresses.</p>
            )}

            {!showForm && !loading && (
                <button
                    onClick={() => setShowForm(true)}
                    className="btn btn-success"
                >
                    Add Address
                </button>
            )}

            {showForm && (
                <div className="mt-4">
                    <h3>Add a New Address</h3>
                    <form onSubmit={handleAddAddress}>
                        <div className="mb-3">
                            <label className="form-label">Address Line 1:</label>
                            <input
                                type="text"
                                name="line"
                                value={newAddress.line}
                                onChange={handleInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Address Line 2:</label>
                            <input
                                type="text"
                                name="line2"
                                value={newAddress.line2}
                                onChange={handleInputChange}
                                className="form-control"
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">City:</label>
                            <input
                                type="text"
                                name="city"
                                value={newAddress.city}
                                onChange={handleInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Country:</label>
                            <input
                                type="text"
                                name="country"
                                value={newAddress.country}
                                onChange={handleInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Postcode:</label>
                            <input
                                type="text"
                                name="postcode"
                                value={newAddress.postcode}
                                onChange={handleInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mt-3">
                            <button type="submit" className="btn btn-success">
                                Add
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowForm(false)}
                                className="btn btn-secondary ms-2"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {showEditForm && editAddressData && (
                <div className="mt-4">
                    <h3>Edit Address</h3>
                    <form onSubmit={handleEditAddress}>
                        <div className="mb-3">
                            <label className="form-label">Address Line 1:</label>
                            <input
                                type="text"
                                name="line"
                                value={editAddressData.line}
                                onChange={handleEditInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Address Line 2:</label>
                            <input
                                type="text"
                                name="line2"
                                value={editAddressData.line2}
                                onChange={handleEditInputChange}
                                className="form-control"
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">City:</label>
                            <input
                                type="text"
                                name="city"
                                value={editAddressData.city}
                                onChange={handleEditInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Country:</label>
                            <input
                                type="text"
                                name="country"
                                value={editAddressData.country}
                                onChange={handleEditInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Postcode:</label>
                            <input
                                type="text"
                                name="postcode"
                                value={editAddressData.postcode}
                                onChange={handleEditInputChange}
                                className="form-control"
                                required
                            />
                        </div>
                        <div className="mt-3">
                            <button type="submit" className="btn btn-success">
                                Update
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowEditForm(false)}
                                className="btn btn-secondary ms-2"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </div>
    );
};

export default AddressTab;
