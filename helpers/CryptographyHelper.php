<?php

namespace app\helpers;

class CryptographyHelper
{
    /**
     * Функция конвертирует формат ключа der в pem
     * {@see https://www.php.net/manual/ru/ref.openssl.php}
     *
     * @param string $derKey
     * @return string
     */
    public static function der2pem(string $derKey): string
    {
        $body = chunk_split($derKey, 64, "\n");
        return "-----BEGIN PUBLIC KEY-----\n{$body}-----END PUBLIC KEY-----";
    }
}
