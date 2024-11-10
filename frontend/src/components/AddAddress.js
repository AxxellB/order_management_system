import React, {useState} from 'react';
import {useNavigate} from 'react-router-dom';
import {useAlert} from '../provider/AlertProvider';
import {addAddress} from '../services/addressService';
import 'bootstrap/dist/css/bootstrap.min.css';

const AddAddress = () => {
    const [addressData, setAddressData] = useState({line: '', city: '', postcode: '', country: ''});
    const navigate = useNavigate();
    const {showAlert} = useAlert();

    const handleChange = (e) => {
        setAddressData({...addressData, [e.target.name]: e.target.value});
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await addAddress(addressData);
            showAlert('Address successfully added!', 'success');
            navigate('/profile/addresses');
        } catch (err) {
            showAlert('Error adding address', 'error');
        }
    };

    return (
        <div className="container mt-5">
            <h2>Add Address</h2>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="line" className="form-label">Address Line</label>
                    <input
                        type="text"
                        id="line"
                        name="line"
                        className="form-control"
                        value={addressData.line}
                        onChange={handleChange}
                    />
                </div>
                <div className="mb-3">
                    <label htmlFor="line2" className="form-label">Address Line 2</label>
                    <input
                        type="text"
                        id="line2"
                        name="line2"
                        className="form-control"
                        value={addressData.line2}
                        onChange={handleChange}
                    />
                </div>
                <div className="mb-3">
                    <label htmlFor="city" className="form-label">City</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        className="form-control"
                        value={addressData.city}
                        onChange={handleChange}
                    />
                </div>
                <div className="mb-3">
                    <label htmlFor="postcode" className="form-label">Postcode</label>
                    <input
                        type="text"
                        id="postcode"
                        name="postcode"
                        className="form-control"
                        value={addressData.postcode}
                        onChange={handleChange}
                    />
                </div>
                <div className="mb-3">
                    <label htmlFor="country" className="form-label">Country</label>
                    <input
                        type="text"
                        id="country"
                        name="country"
                        className="form-control"
                        value={addressData.country}
                        onChange={handleChange}
                    />
                </div>
                <button type="submit" className="btn btn-primary">Add Address</button>
            </form>
        </div>
    );
};

export default AddAddress;
