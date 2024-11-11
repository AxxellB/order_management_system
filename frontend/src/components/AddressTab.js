import React, {useEffect, useState} from 'react';
import {getAddresses, deleteAddress} from '../services/addressService';
import {useAlert} from "../provider/AlertProvider";
import {useAuth} from "../provider/AuthProvider";
import {Link} from 'react-router-dom';

const AddressTab = () => {
    const [addresses, setAddresses] = useState([]);
    const [loading, setLoading] = useState(false);
    const {showAlert} = useAlert();

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

        const delayFetch = setTimeout(fetchAddresses, 50);
        return () => clearTimeout(delayFetch);
    }, []);

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

    return (
        <div className="container mt-4">

            <h2 className="mb-4">My Addresses</h2>

            {loading ? (
                <div className="d-flex justify-content-center">
                    <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Loading...</span>
                    </div>
                </div>
            ) : (
                <>
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
                                            <Link to={`/profile/addresses/edit/${address.id}`}
                                                  className="btn btn-primary ">
                                                Edit
                                            </Link>
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
                        ) : (
                            <p>You don't have any addresses.</p>
                        )}
                    </div>

                    <div className="mt-4">
                        <Link to="/profile/addresses/new" className="btn btn-primary">
                            Add New Address
                        </Link>
                    </div>
                </>
            )}


        </div>
    );
};

export default AddressTab;
