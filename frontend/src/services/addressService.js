import axios from "axios";

const getAddresses = async () => {
    try {
        const response = await axios.get('/api/addresses');
        return response.data.addresses;
    } catch (error) {
        console.error("Error fetching addresses:", error);
        throw new Error('Failed to load addresses');
    }
};

const addAddress = async (address) => {
    try {
        const response = await axios.post('/api/addresses', address);
        if (response.status === 201) {
            alert("Address added successfully");
        } else {
            alert("Failed to add address");
        }
    } catch (error) {
        console.log(error);
    }
};

const editAddress = async (addressId, updatedAddress) => {
    try {
        const response = await axios.put(`/api/address/${addressId}`, updatedAddress);
        if (response.status === 200) {
            alert("Address updated successfully");
        } else {
            alert("Failed to update address");
        }
    } catch (error) {
        console.log(error);
    }
};

const deleteAddress = async (addressId) => {
    try {
        const response = await axios.delete(`/api/address/${addressId}`);
        if (response.status === 204) {
            alert('Address deleted successfully');
        } else {
            alert('Failed to delete address');
        }
    } catch (error) {
        console.log(error);
    }
};

export { getAddresses, addAddress, editAddress, deleteAddress };