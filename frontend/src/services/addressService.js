import axios from "axios";

const getAddresses = async () => {
    const response = await axios.get('/api/addresses');
    return response.data.addresses;
};

const addAddress = async (address) => {
    await axios.post('/api/addresses', address);
};

const editAddress = async (addressId, updatedAddress) => {
    await axios.put(`/api/address/${addressId}`, updatedAddress);
};

const deleteAddress = async (addressId) => {
    await axios.delete(`/api/address/${addressId}`);
};

const getSingleAddress = async (addressId) => {
    const response = await axios.get(`/api/address/${addressId}`);
    return response.data.address;
};

export {getAddresses, addAddress, editAddress, deleteAddress, getSingleAddress};