<?php

$keyPairResource = openssl_pkey_new([
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);

openssl_pkey_export($keyPairResource, $privateKey);

$publicKeyDetails = openssl_pkey_get_details($keyPairResource);
$publicKey = $publicKeyDetails['key'];

file_put_contents('config/encryption/private_key.pem', $privateKey);
chmod('config/encryption/private_key.pem', 0600);

file_put_contents('config/encryption/public_key.pem', $publicKey);
chmod('/config/encryption/public_key.pem', 0644);

echo "Keys generated and saved successfully.\n";
