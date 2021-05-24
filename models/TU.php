<?php


namespace app\models;


class TU
{
    public static $JKH = 0; //оплата жкх
    public static $REGCARD = 1; //регистрация карты
    public static $ECOM = 2; //оплата еком

    public static $TOSCHET = 11; //выдача займа на счет физ. лица
    public static $TOCARD = 13; // выдача займа на карту физ. лица.
    public static $POGASHATF = 10; //погашение афт
    public static $AVTOPLATATF = 12; //автоплатеж афт
    public static $POGASHECOM = 14; //погашение еком
    public static $AVTOPLATECOM = 16; //автоплатеж еком
    public static $VYPLATVOZN = 17; //вывод вознаграждения
    public static $VYVODPAYS = 19; //перечисление платежей
    public static $REVERSCOMIS = 21; //возмещение комиссии
    public static $PEREVPAYS = 23; //перевод на выдачу

    public static $JKHPARTS = 100; //оплата жкх с разбивкой
    public static $ECOMPARTS = 102; //оплата еком с разбивкой
    public static $POGASHATFPARTS = 110; //погашение афт с разбивкой
    public static $AVTOPLATATFPARTS = 112; //автоплатеж афт с разбивкой
    public static $POGASHECOMPARTS = 114; //погашение еком с разбивкой
    public static $AVTOPLATECOMPARTS = 116; //автоплатеж еком с разбивкой
    public static $VYVODPAYSPARTS = 119; //перечисление по разбивке


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
        return [
            self::$JKH, self::$REGCARD, self::$ECOM, self::$POGASHATF, self::$AVTOPLATATF, self::$POGASHECOM, self::$AVTOPLATECOM,
            self::$JKHPARTS, self::$ECOMPARTS, self::$POGASHATFPARTS, self::$POGASHECOMPARTS,
        ];
    }

    public static function IsInPay($type)
    {
        return in_array($type, self::InPay());
    }

    public static function InPay()
    {
        return [
            self::$JKH, self::$REGCARD, self::$ECOM, self::$POGASHATF, self::$POGASHECOM,
            self::$JKHPARTS, self::$ECOMPARTS, self::$POGASHATFPARTS, self::$POGASHECOMPARTS,
        ];
    }

    public static function IsInMfo($type)
    {
        return in_array($type, self::InMfo());
    }

    public static function InMfo()
    {
        return [
            self::$POGASHATF, self::$AVTOPLATATF, self::$POGASHECOM, self::$AVTOPLATECOM,
            self::$POGASHATFPARTS, self::$AVTOPLATATFPARTS, self::$POGASHECOMPARTS, self::$AVTOPLATECOMPARTS,
        ];
    }

    public static function IsInAuto($type)
    {
        return in_array($type, [self::$AVTOPLATATF, self::$AVTOPLATECOM]);
    }

    public static function IsInAutoAll($type)
    {
        return in_array($type, self::AutoPay());
    }

    public static function AutoPay()
    {
        return [
            self::$AVTOPLATATF, self::$AVTOPLATECOM,
            self::$AVTOPLATATFPARTS, self::$AVTOPLATECOMPARTS,
        ];
    }

    public static function NoPart()
    {
        return [self::$REGCARD, self::$VYVODPAYS, self::$VYPLATVOZN, self::$REVERSCOMIS, self::$PEREVPAYS, self::$VYVODPAYSPARTS];
    }

}
