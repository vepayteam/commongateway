<?php

namespace app\services\yandex;

use app\helpers\CryptographyHelper;
use app\services\yandex\exceptions\PaymentTokenVerifyServiceException;
use app\services\yandex\models\PaymentToken;

class PaymentTokenVerifyService
{
    private const SENDER_ID = 'Yandex';

    /**
     * @param PaymentToken $paymentToken
     * @return void
     * @throws PaymentTokenVerifyServiceException
     * @throws \yii\base\InvalidConfigException
     * @throws exceptions\RootKeyStorageServiceException
     */
    public function verify(PaymentToken $paymentToken)
    {
        $this->verifyIntermediateSigningKey($paymentToken);
        $this->verifySignedMessage($paymentToken);
    }

    /**
     * @param PaymentToken $paymentToken
     * @return void
     * @throws PaymentTokenVerifyServiceException
     * @throws \yii\base\InvalidConfigException
     * @throws exceptions\RootKeyStorageServiceException
     */
    private function verifyIntermediateSigningKey(PaymentToken $paymentToken)
    {
        /** @var RootKeyStorageService $rootKeyStorageService */
        $rootKeyStorageService = \Yii::$app->get(RootKeyStorageService::class);

        $toVerifyItermKey = $this->getValidationString([
            self::SENDER_ID,
            $paymentToken->getProtocolVersion(),
            $paymentToken->getIntermediateSigningKey()->getJsonSignedKey(),
        ]);

        $rootKeys = $rootKeyStorageService->getKeys();
        foreach ($rootKeys as $rootKey) {
            $keyExpired = $this->checkKeyExpiration($rootKey->getKeyExpiration());
            if ($keyExpired) {
                throw new PaymentTokenVerifyServiceException('Root key expired ' . $rootKey->keyValue);
            }

            $pemPublicKey = CryptographyHelper::der2pem($rootKey->getRawKeyValue());
            $publicKey = openssl_get_publickey($pemPublicKey);

            foreach ($paymentToken->getIntermediateSigningKey()->getDecodedSignatures() as $signature) {
                $verify = openssl_verify($toVerifyItermKey, $signature, $publicKey, 'sha256');
                if ($verify === 1) {
                    return;
                }
            }
        }

        throw new PaymentTokenVerifyServiceException('Failed to validate intermediate signing key');
    }

    /**
     * @param PaymentToken $paymentToken
     * @return void
     * @throws PaymentTokenVerifyServiceException
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
            throw new PaymentTokenVerifyServiceException('Intermediate key expired ' . $signedKey->getRawKeyValue());
        }

        $pemPublicKey = CryptographyHelper::der2pem($signedKey->getRawKeyValue());
        $publicKey = openssl_get_publickey($pemPublicKey);

        $verify = openssl_verify($toVerifySignedMessage, $paymentToken->getDecodedSignature(), $publicKey, 'sha256');
        if ($verify !== 1) {
            throw new PaymentTokenVerifyServiceException('Failed to verify signed message');
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
