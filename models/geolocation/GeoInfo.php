<?php


namespace app\models\geolocation;

use app\models\Country;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoInfo
{
    /**
     * @var null|Reader
     */
    private $gi;

    public function __construct()
    {
        try {
            $this->gi = new Reader(__dir__ . '/GeoLite2-City.mmdb');
        } catch (InvalidDatabaseException $e) {
            \Yii::error('GeoInfo: Unable to instantiate the geo data reader.');
            $this->gi = null;
        }
    }

    /**
     * Получить город по IP
     * @return string
     */
    public function getCity(?string $ip): ?string
    {
        $city = null;
        if ($this->gi) {
            try {
                // Yii::$app->request->userIP ?: Yii::$app->request->remoteIP
                $record = $this->gi->city($ip);
                $city = $record->city->names['en'] ?? null;
            } catch (AddressNotFoundException | InvalidDatabaseException $e) {
            }
        }
        return $city;
    }

    /**
     * Получить страну по IP
     * @return string
     */
    public function getCountry(?string $ip): ?string
    {
        $country = null;
        if ($this->gi) {
            try {
                $record = $this->gi->city($ip);
                $country = $record->country->isoCode ?? null;
            } catch (AddressNotFoundException | InvalidDatabaseException $e) {
            }
        }

        // Преобразуем код страны из формата Alpha2 в Alpha3
        if ($country !== null) {
            $countryModel = Country::findOne(['Alpha2' => strtoupper($country)]);
            if ($countryModel !== null) {
                $country = $countryModel->Alpha3;
            }
        }

        return $country;
    }
}