<?php
function encryptXOR($plaintext, $key) {
    $result = '';
    for ($i = 0; $i < strlen($plaintext); $i++) {
        $result .= chr(ord($plaintext[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($result);
}

function decryptXOR($ciphertext, $key) {
    $ciphertext = base64_decode($ciphertext); // Tambahkan ini
    $result = '';
    for ($i = 0; $i < strlen($ciphertext); $i++) {
        $result .= chr(ord($ciphertext[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return $result;
}

