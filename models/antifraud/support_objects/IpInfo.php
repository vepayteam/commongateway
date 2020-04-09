<?php


namespace app\models\antifraud\support_objects;


use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Yii;

/**
 * @property Reader $reader_city
 * @property Reader $reader_asn
 */
class IpInfo
{

    private $reader_city;
    private $reader_asn;

    public function __construct()
    {
        $db_city = Yii::getAlias('@app') . '/models/geolocation/GeoLite2-City.mmdb';
        $this->reader_city = new Reader($db_city);
        $db_asn = $db_path = Yii::getAlias('@app') . '/models/geolocation/GeoLite2-ASN.mmdb';
        $this->reader_asn = new Reader($db_asn);
    }

    public function ip(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip == '127.0.0.1') {
            return $ip = '176.226.142.218'; //мой ip
        }
        return $ip;
    }

    public function country_iso(): string
    {
        $ip = $this->ip();
        try{
            $country = $this->reader_city->city($ip)->country->isoCode;
        }catch (AddressNotFoundException $e){
            $country = '';
        }
        switch ($country) {
            default:
                return $country;
            case "RU":
                return "RUS";
        }
    }

    public function asn(): string
    {
        $ip = $this->ip();
        return $this->asn_by_ip($ip);
    }

    public function time_zone(string $ip): string
    {
        try{
            return $this->reader_city->city($ip)->location->timeZone;
        }
        catch (AddressNotFoundException $e){
            return '';
        }
    }

    public function asn_by_ip(string $ip)
    {
        try{
            $asn = $this->reader_asn->asn($ip)->autonomousSystemNumber;
            if ($asn) {
                return 'AS' . $asn;
            }
            return '';
        }catch (AddressNotFoundException $e){
            return '';
        }
    }
}