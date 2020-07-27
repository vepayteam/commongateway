<?php


namespace app\models;


class TU
{
    public static $JKH = 0; //оплата жкх
    public static $REGCARD = 1; //регистрация карты
    public static $ECOM = 2; //оплата еком

    public static $TOSCHET = 11; //выдача займа на счет физ. лица
    public static $TOCARD = 13; // выдача займа на карту физ. лица.
    public static $POGASHATF = 10; //покашение афт
    public static $AVTOPLATATF = 12; //автоплатеж афт
    public static $POGASHECOM = 14; //покашение еком
    public static $AVTOPLATECOM = 16; //автоплатеж еком
    public static $VYPLATVOZN = 17; //вывод вознаграждения
    public static $VYVODPAYS = 19; //перечисление платежей
    public static $REVERSCOMIS = 21; //возмещение комиссии
    public static $PEREVPAYS = 23; //перевод на выдачу

    public static function IsOutMfo($type)
    {
        return in_array($type, self::OutMfo());
    }

    public static function OutMfo()
    {
        return [self::$TOSCHET, self::$TOCARD];
    }

    public static function IsInAll($type)
    {
        return in_array($type, self::InAll());
    }

    public static function InAll()
    {
        return [self::$JKH, self::$REGCARD, self::$ECOM, self::$POGASHATF, self::$AVTOPLATATF, self::$POGASHECOM, self::$AVTOPLATECOM];
    }

    public static function InAllBySaledraft()
    {
        return array_merge([self::$TOCARD], self::InAll());
    }

    public static function IsInPay($type)
    {
        return in_array($type, self::InPay());
    }

    public static function InPay()
    {
        return [self::$JKH, self::$REGCARD, self::$ECOM, self::$POGASHATF, self::$POGASHECOM];
    }

    public static function IsInMfo($type)
    {
        return in_array($type, self::InMfo());
    }

    public static function InMfo()
    {
        return [self::$POGASHATF, self::$AVTOPLATATF, self::$POGASHECOM, self::$AVTOPLATECOM];
    }

    public static function IsInAuto($type)
    {
        return in_array($type, [self::$AVTOPLATATF,self::$AVTOPLATECOM]);
    }

    public static function AutoPay()
    {
        return [self::$AVTOPLATATF,self::$AVTOPLATECOM];
    }

    public static function NoPart()
    {
        return [self::$REGCARD, self::$VYVODPAYS, self::$VYPLATVOZN, self::$REVERSCOMIS, self::$PEREVPAYS];
    }

}
