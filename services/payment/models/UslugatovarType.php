<?php

namespace app\services\payment\models;

/**
 * This is the model class for table "uslugatovar_types".
 *
 * @property int|null $Id
 * @property string $Name
 * @property int|null $DefaultBankId
 */
class UslugatovarType extends \yii\db\ActiveRecord
{
    const JKH = 0; //оплата жкх
    const REGCARD = 1; //регистрация карты
    const ECOM = 2; //оплата еком

    const TOSCHET = 11; //выдача займа на счет физ. лица
    const TOCARD = 13; // выдача займа на карту физ. лица.
    const POGASHATF = 10; //покашение афт
    const AVTOPLATATF = 12; //автоплатеж афт
    const POGASHECOM = 14; //покашение еком
    const AVTOPLATECOM = 16; //автоплатеж еком
    const VYPLATVOZN = 17; //вывод вознаграждения
    const VYVODPAYS = 19; //перечисление платежей
    const REVERSCOMIS = 21; //возмещение комиссии
    const PEREVPAYS = 23; //перевод на выдачу
    const IDENT = 24; //идентификация

    const JKHPARTS = 100; //оплата жкх с разбивкой
    const ECOMPARTS = 102; //оплата еком с разбивкой
    const POGASHATFPARTS = 110; //покашение афт с разбивкой
    const AVTOPLATATFPARTS = 112; //автоплатеж афт с разбивкой
    const POGASHECOMPARTS = 114; //покашение еком с разбивкой
    const AVTOPLATECOMPARTS = 116; //автоплатеж еком с разбивкой
    const VYVODPAYSPARTS = 119; //перечисление по разбивке

    /** H2H Погашение займа AFT  */
    public const H2H_POGASH_AFT = 200;
    /** H2H погашение займа ECOM */
    public const H2H_POGASH_ECOM = 201;
    /** H2H оплата товаров и услуг */
    public const H2H_ECOM = 202;

    /** Перевод B2C СБП  */
    public const TRANSFER_B2C_SBP = 203;

    /**
     * {@inheritDoc}
     */
    public static function tableName(): string
    {
        return 'uslugatovar_types';
    }

    /**
     * Возвращает список всех типов.
     *
     * @return string[] Вернет массив: [ID типа => Название типа].
     */
    public static function typeList(): array
    {
        return [
            self::REGCARD => 'Регистрация карты',
            self::TOSCHET => 'Выплата на счет',
            self::TOCARD => 'Выдача займа на карту',
            self::POGASHATF => 'Погашение займа AFT',
            self::POGASHECOM => 'Погашение займа ECOM',
            self::AVTOPLATECOM => 'Автоплатеж по займу ECOM',
            self::AVTOPLATATF => 'Автоплатеж по займу AFT',
            self::ECOM => 'Оплата товара/услуги',
            self::JKH => 'Оплата ЖКХ',
            self::VYPLATVOZN => 'Комиссия',
            self::VYVODPAYS => 'Вывод средств',
            self::REVERSCOMIS => 'Возмещение комисии',
            self::PEREVPAYS => 'Внутренний перевод между счетами',
            self::IDENT => 'Упрощенная идентификация пользователей',

            self::POGASHATFPARTS => 'Погашение займа AFT с разбивкой',
            self::POGASHECOMPARTS => 'Погашение займа ECOM с разбивкой',
            self::AVTOPLATECOMPARTS => 'Автоплатеж по займу ECOM с разбивкой',
            self::AVTOPLATATFPARTS => 'Автоплатеж по займу AFT с разбивкой',
            self::ECOMPARTS => 'Оплата товара/услуги с разбивкой',
            self::JKHPARTS => 'Оплата ЖКХ с разбивкой',
            self::VYVODPAYSPARTS => 'Перечисление по разбивке',

            self::H2H_POGASH_AFT => 'H2H Погашение займа AFT',
            self::H2H_POGASH_ECOM => 'H2H погашение займа ECOM',
            self::H2H_ECOM => 'H2H оплата товаров и услуг',
            self::TRANSFER_B2C_SBP => 'Перевод B2C SBP',
        ];
    }

    /**
     * @return int[]
     */
    public static function outTypes(): array
    {
        return [
            self::TOSCHET,
            self::TOCARD,
        ];
    }

    /**
     * @return int[]
     */
    public static function autoTypes(): array
    {
        return [
            self::AVTOPLATECOM,
            self::AVTOPLATATF,
        ];
    }

    /**
     * @return int[]
     */
    public static function recurrentTypes(): array
    {
        return [
            self::AVTOPLATECOM,
            self::AVTOPLATATF,
            self::AVTOPLATECOMPARTS,
            self::AVTOPLATATFPARTS,
        ];
    }

    /**
     * Типы для оплаты через ECOM.
     *
     * @return int[]
     */
    public static function ecomTypes(): array
    {
        return [
            self::ECOM,
            self::ECOMPARTS,
            self::POGASHECOM,
            self::AVTOPLATECOM,
            self::AVTOPLATECOMPARTS,
            self::H2H_POGASH_ECOM,
            self::H2H_ECOM,
        ];
    }
}
