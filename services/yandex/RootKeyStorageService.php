<?php

namespace app\services\yandex;

use app\Api\Client\Client;
use app\models\YandexPayRootKey;
use app\services\yandex\exceptions\RootKeyStorageServiceException;
use app\services\yandex\models\RootKey;
use GuzzleHttp\Exception\GuzzleException;
use yii\helpers\Json;

class RootKeyStorageService
{
    private const KEYS_ENDPOINT = '/api/v1/keys/keys.json';

    /**
     * @return RootKey[]
     * @throws RootKeyStorageServiceException
     */
    public function getKeys(): array
    {
        $yandexPayRootKeys = YandexPayRootKey::find()->all();
        if (count($yandexPayRootKeys) === 0) {
            \Yii::info('RootKeyStorageService getKeys no keys found in db');
            $this->updateKeys();
        }

        return array_map(function (YandexPayRootKey $key) {
            return new RootKey([
                'protocolVersion' => $key->protocolVersion,
                'keyValue' => $key->keyValue,
                'keyExpiration' => $key->keyExpiration,
            ]);
        }, $yandexPayRootKeys);
    }

    /**
     * @return void
     * @throws RootKeyStorageServiceException
     */
    public function updateKeys()
    {
        try {
            $keys = $this->fetchKeys();
        } catch (\Exception|GuzzleException $e) {
            throw new RootKeyStorageServiceException('Error loading keys', 0, $e);
        }

        if ($keys === null || count($keys) === 0) {
            \Yii::error('RootKeyStorageService updateKeys bad root keys response: ' . Json::encode($keys));
            throw new RootKeyStorageServiceException('Bad root keys response: ' . Json::encode($keys));
        }

        foreach ($keys as $key) {
            $yandexPayRootKey = YandexPayRootKey::findOne(['keyValue' => $key['keyValue']]);
            if (!$yandexPayRootKey) {
                $yandexPayRootKey = new YandexPayRootKey([
                    'keyValue' => $key['keyValue'],
                    'keyExpiration' => $key['keyExpiration'],
                    'protocolVersion' => $key['protocolVersion'],
                ]);
                $yandexPayRootKey->save();
            }
        }

        /**
         * Ключи, которых нет в списке удаляем
         */
        YandexPayRootKey::deleteAll([
            'not in',
            'keyValue',
            array_column($keys, 'keyValue'),
        ]);
    }

    /**
     * @return array|null
     * @throws GuzzleException
     * @throws \yii\base\InvalidConfigException
     */
    private function fetchKeys(): ?array
    {
        /** @var PaymentHandlerService $paymentHandlerService */
        $paymentHandlerService = \Yii::$app->get(PaymentHandlerService::class);

        $client = new Client();
        $response = $client->request('GET', $paymentHandlerService->getEndpoint(self::KEYS_ENDPOINT));

        return $response->json('keys');
    }
}
