<?php


namespace app\models\geolocation;

use app\models\payonline\UslugiRegions;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Yii;

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
            $this->gi = null;
        }
    }

    /**
     * Получить регион по IP
     * @return UslugiRegions|array|null|\yii\db\ActiveRecord
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function getSityId()
    {
        if ($this->gi) {
            try {
                $record = $this->gi->city(Yii::$app->request->userIP ?: Yii::$app->request->remoteIP);
                return UslugiRegions::find()->where(
                    'NameRegion LIKE :CITY', [
                    ':CITY' => "%" . $record->city->names['ru'] . "%"
                ])->one();
            } catch (AddressNotFoundException $e) {
            }
        }
        return null;
    }

    /**
     * Получить город по IP
     * @return UslugiRegions|array|null|\yii\db\ActiveRecord
     */
    public function GetCity()
    {
        $city = '';
        if ($this->gi) {
            try {
                $record = $this->gi->city(Yii::$app->request->userIP ?: Yii::$app->request->remoteIP);
                $city = isset($record->city->names['ru']) ? $record->city->names['ru'] : '';
            } catch (AddressNotFoundException $e) {
            } catch (InvalidDatabaseException $e) {
            }
        }
        return $city;
    }

    /**
     * Получить страну по IP
     * @return UslugiRegions|array|null|\yii\db\ActiveRecord
     */
    public function GetCountry()
    {
        $city = '';
        if ($this->gi) {
            try {
                $record = $this->gi->city(Yii::$app->request->userIP ?: Yii::$app->request->remoteIP);
                $city = isset($record->country->names['ru']) ? $record->country->names['ru'] : '';
            } catch (AddressNotFoundException $e) {
            } catch (InvalidDatabaseException $e) {
            }
        }
        return $city;
    }
}