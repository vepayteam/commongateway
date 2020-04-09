<?php


namespace app\models\crypt;


class Crypt
{
    /**
     * Encrypt
     * @param string $plaintext
     * @param string $key
     * @param string $cipher
     * @param string $hash
     * @return string
     */
    public function encrypt($plaintext, $key, $cipher = "AES-128-CBC", $hash = 'sha256')
    {
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac($hash, $ciphertext_raw, $key, $as_binary=true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
        return $ciphertext;
    }

    /**
     * Decrypt
     * @param string $ciphertext
     * @param string $key
     * @param string $cipher
     * @param string $hash
     * @return bool|string
     */
    public function decrypt($ciphertext, $key, $cipher = "AES-128-CBC", $hash = 'sha256')
    {
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac($hash, $ciphertext_raw, $key, $as_binary=true);
        if (hash_equals($hmac, $calcmac)) {
            return $plaintext;
        }
        return false;
    }
}