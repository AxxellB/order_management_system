document.addEventListener('DOMContentLoaded', handleAddressChange)

function handleAddressChange(){
    const selectedAddress = document.getElementById('edit_user_form_selectAddress');
    selectedAddress.addEventListener('change', handleInputChange)

    function handleInputChange() {
        const addressId = selectedAddress.value;

        if (addressId) {
            fetch(`/user/me/get-address/${addressId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_user_form_addressDetails_line').value = data.line;
                    document.getElementById('edit_user_form_addressDetails_city').value = data.city;
                    document.getElementById('edit_user_form_addressDetails_country').value = data.country;
                    document.getElementById('edit_user_form_addressDetails_postcode').value = data.postcode;
                });
        } else {
            document.getElementById('edit_user_form_addressDetails_line').value = '';
            document.getElementById('edit_user_form_addressDetails_city').value = '';
            document.getElementById('edit_user_form_addressDetails_country').value = '';
            document.getElementById('edit_user_form_addressDetails_postcode').value = '';
        }
    }
}


