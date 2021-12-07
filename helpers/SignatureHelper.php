<?php

namespace app\helpers;

use app\helpers\signatureHelper\SignatureException;

class SignatureHelper
{
    /**
     * Creates a signature by the specified string and the private key.
     *
     * @param string $stringToSign
     * @param string $privateKey Private key text.
     * @param int $algorithm
     * @return string Base64-encoded signature.
     * @throws SignatureException
     */
    public static function sign(string $stringToSign, string $privateKey, int $algorithm = OPENSSL_ALGO_SHA256): string
    {
        $success = openssl_sign($stringToSign, $rawSignature, $privateKey, $algorithm);
        if (!$success) {
            throw new SignatureException(openssl_error_string());
        }

        return base64_encode($rawSignature);
    }
}