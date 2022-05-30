<?php

namespace app\services\yandexPay\paymentToken;

use app\helpers\CryptographyHelper;
use app\models\YandexPayRootKey;
use app\services\yandexPay\models\PaymentToken;
use app\services\yandexPay\YandexPayException;

class PaymentTokenVerify
{
    private const SENDER_ID = 'Yandex';

    /**
     * @param PaymentToken $paymentToken
     * @param YandexPayRootKey[] $rootKeys
     * @return void
     * @throws YandexPayException
     */
    public function validate(PaymentToken $paymentToken, array $rootKeys)
    {
        $this->verifyIntermediateSigningKey($paymentToken, $rootKeys);
        $this->verifySignedMessage($paymentToken);
    }

    /**
     * @param PaymentToken $paymentToken
     * @param YandexPayRootKey[] $rootKeys
     * @return void
     * @throws YandexPayException
     */
    private function verifyIntermediateSigningKey(PaymentToken $paymentToken, array $rootKeys)
    {
        $toVerifyItermKey = $this->getValidationString([
            self::SENDER_ID,
            $paymentToken->getProtocolVersion(),
            $paymentToken->getIntermediateSigningKey()->getJsonSignedKey(),
        ]);

        foreach ($rootKeys as $rootKey) {
            $keyExpired = $this->checkKeyExpiration($rootKey->keyExpiration);
            if ($keyExpired) {
                throw new YandexPayException('Root key expired ' . $rootKey->keyValue);
            }

            $pemPublicKey = CryptographyHelper::der2pem($rootKey->keyValue);
            $publicKey = openssl_get_publickey($pemPublicKey);

            foreach ($paymentToken->getIntermediateSigningKey()->getDecodedSignatures() as $signature) {
                $verify = openssl_verify($toVerifyItermKey, $signature, $publicKey, 'sha256');
                if ($verify === 1) {
                    return;
                }
            }
        }

        throw new YandexPayException('Failed to validate intermediate signing key');
    }

    /**
     * @param PaymentToken $paymentToken
     * @return void
     * @throws YandexPayException
     */
    private function verifySignedMessage(PaymentToken $paymentToken)
    {
        $toVerifySignedMessage = $this->getValidationString([
            self::SENDER_ID,
            'vepay',
            $paymentToken->getProtocolVersion(),
            $paymentToken->getJsonSignedMessage(),
        ]);;

        $signedKey = $paymentToken->getIntermediateSigningKey()->getSignedKey();

        $keyExpired = $this->checkKeyExpiration($signedKey->getKeyExpiration());
        if ($keyExpired) {
            throw new YandexPayException('Intermediate key expired ' . $signedKey->getRawKeyValue());
        }

        $pemPublicKey = CryptographyHelper::der2pem($signedKey->getRawKeyValue());
        $publicKey = openssl_get_publickey($pemPublicKey);

        $verify = openssl_verify($toVerifySignedMessage, $paymentToken->getDecodedSignature(), $publicKey, 'sha256');
        if ($verify !== 1) {
            throw new YandexPayException('Failed to verify signed message');
        }
    }

    /**
     * @param int $keyExpiration
     * @return bool
     */
    private function checkKeyExpiration(int $keyExpiration): bool
    {
        $now = (int)(time() * 1000);
        return $now >= $keyExpiration;
    }

    /**
     * @param string[] $parameters
     * @return string
     */
    private function getValidationString(array $parameters): string
    {
        return join(array_map(function (string $item) {
            return pack('V', strlen($item)) . $item;
        }, $parameters), '');
    }
}