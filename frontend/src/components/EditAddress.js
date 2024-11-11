import React, {useState, useEffect} from 'react';
import {useParams, useNavigate} from 'react-router-dom';
import {useAlert} from '../provider/AlertProvider';
import {getSingleAddress, editAddress} from '../services/addressService';

const EditAddress = () => {
    const {id} = useParams();
    const [editAddressData, setEditAddressData] = useState({line: '', city: '', postcode: '', country: ''});
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const {showAlert} = useAlert();
    const navigate = useNavigate();

    useEffect(() => {
        const fetchAddress = async () => {
            try {
                const response = await getSingleAddress(id);
                setEditAddressData(response);
            } catch (err) {
                showAlert('Error fetching address data', 'error');
            }
        };
        fetchAddress();
    }, [id, showAlert]);

    const handleEditInputChange = (e) => {
        setEditAddressData({...editAddressData, [e.target.name]: e.target.value});
    };

    const handleEditAddress = async (e) => {
        e.preventDefault();
        setLoading(true);
        const {id, ...addressData} = editAddressData;
        try {
            await editAddress(id, addressData);
            showAlert('Address updated successfully', 'success');
            setError(null);
            navigate('/profile/addresses');
        } catch {
            showAlert('Failed to update address', 'error');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="mt-4 w-25 p-3 mx-auto">
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
                    <button type="submit" className="btn btn-success" disabled={loading}>
                        {loading ? 'Updating...' : 'Update'}
                    </button>
                    <button
                        type="button"
                        onClick={() => navigate('/profile/addresses')}
                        className="btn btn-secondary ms-2"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    );
};

export default EditAddress;
