<?php


namespace app\services\payment\helpers;


class ADGroupBankHelper
{
    public static function hashDataInBase64($data)
    {
        return base64_encode(hash('sha256', $data));
    }

    public static function prepareKey($rawKey)
    {
        return base64_encode($rawKey);
    }

    public static function encryptDataInBase64($data, $key)
    {
        $prepareKey = self::prepareKey($key);
        $encrypted = openssl_encrypt($data, 'aes-128-ecb', $prepareKey);
        return base64_encode($encrypted);
    }

    public static function createSignature($data, $key)
    {
        $prepareKey = self::prepareKey($key);
        $encrypted = openssl_encrypt($data, 'aes-128-ecb', $prepareKey);
        return hash('sha256', $encrypted);
    }

}
