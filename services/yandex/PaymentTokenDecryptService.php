<?php

namespace app\services\yandex;

use app\services\yandex\exceptions\PaymentTokenDecryptServiceException;
use app\services\yandex\models\EncryptionKeyReader;
use app\services\yandex\models\PaymentToken;
use Mdanter\Ecc\Crypto\Key\PublicKey;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Primitives\GeneratorPoint;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Util\NumberSize;

class PaymentTokenDecryptService
{
    /**
     * @param PaymentToken $paymentToken
     * @param EncryptionKeyReader $encryptionKeyReader
     * @return string
     * @throws PaymentTokenDecryptServiceException
     */
    public function decrypt(PaymentToken $paymentToken, EncryptionKeyReader $encryptionKeyReader): string
    {
        $signedMessage = $paymentToken->getSignedMessage();
        $encryptedMessage = $signedMessage->getDecodedEncryptedMessage();

        $adapter = EccFactory::getAdapter();
        $generator = EccFactory::getNistCurves()->generator256();

        $derPublicSerializer = new DerPublicKeySerializer($adapter);
        $derPrivateSerializer = new DerPrivateKeySerializer($adapter, $derPublicSerializer);

        // Load private key
        $pemPrivateSerializer = new PemPrivateKeySerializer($derPrivateSerializer);
        $privateKey = $pemPrivateSerializer->parse($encryptionKeyReader->readPrivateEncryptionKey());

        // Load public key
        $pointerSerializer = new UncompressedPointSerializer();
        $point = $pointerSerializer->unserialize($generator->getCurve(), bin2hex($signedMessage->getDecodedEphemeralPublicKey()));
        $publicKey = new PublicKey($adapter, $generator, $point);

        $exchange = $privateKey->createExchange($publicKey);
        $sharedKey = $exchange->calculateSharedKey();

        $hash = hash_hkdf(
            'sha256',
            $signedMessage->getDecodedEphemeralPublicKey() . $this->kdf($generator, $sharedKey),
            64,
            'Yandex'
        );

        $symmetricEncryptionKey = substr($hash, 0, 32);
        $macKey = substr($hash, 32, 32);

        $hashInit = hash_init('sha256', HASH_HMAC, $macKey);
        hash_update($hashInit, $encryptedMessage);
        $final = hash_final($hashInit, true);

        if ($final !== $signedMessage->getDecodedTag()) {
            throw new PaymentTokenDecryptServiceException('Failed to verify tag');
        }

        $decryptedMessage = openssl_decrypt($encryptedMessage, 'aes-256-ctr', $symmetricEncryptionKey, OPENSSL_RAW_DATA);
        if (!$decryptedMessage) {
            throw new PaymentTokenDecryptServiceException('Failed to decrypt message');
        }

        return $decryptedMessage;
    }

    /**
     * @param GeneratorPoint $point
     * @param \GMP $sharedSecret
     * @return string
     */
    private function kdf(GeneratorPoint $point, \GMP $sharedSecret): string
    {
        $adapter = $point->getAdapter();
        return $adapter->intToFixedSizeString(
            $sharedSecret,
            NumberSize::bnNumBytes($adapter, $point->getOrder())
        );
    }
}
