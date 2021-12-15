<?php

namespace app\services\payment\models;

use Yii;

/**
 * This is the model class for table "uslugatovar_types".
 *
 * @property int|null $Id
 * @property string $Name
 * @property int|null $DefaultBankId
 */
class UslugatovarType extends \yii\db\ActiveRecord
{
    public const JKH = 0; // Оплата ЖКХ
    public const REGCARD = 1; // Привязка карты
    public const ECOM = 2; // Оплата товара/услуги
    public const POGASHATF = 10; // Платёж AFT
    public const TOSCHET = 11; // Выплата на счет
    public const AVTOPLATATF = 12; // Автоплатёж AFT
    public const TOCARD = 13; // Выплата на карту
    public const POGASHECOM = 14; // Платёж ECOM
    public const AVTOPLATECOM = 16; // Автоплатёж ECOM
    public const VYPLATVOZN = 17; // Вывод комиссии VEPAY
    public const VYVODPAYS = 19; // Вывод средств на р/сч
    public const REVERSCOMIS = 21; // Возмещение комисии
    public const PEREVPAYS = 23; // Внутренний перевод между счетами
    public const IDENT = 24; // Упрощенная идентификация пользователей
    public const P2P = 26; // P2P перевод с карты на карту
    public const JKHPARTS = 100; // Оплата ЖКХ с разбивкой
    public const ECOMPARTS = 102; // Оплата товара/услуги с разбивкой
    public const POGASHATFPARTS = 110; // Платёж AFT с разбивкой
    public const AVTOPLATATFPARTS = 112; // Автоплатёж AFT с разбивкой
    public const POGASHECOMPARTS = 114; // Платёж ECOM с разбивкой
    public const AVTOPLATECOMPARTS = 116; // Автоплатёж ECOM с разбивкой
    public const VYVODPAYSPARTS = 119; // Перечисление по разбивке
    public const H2H_POGASH_AFT = 200; // H2H платёж AFT
    public const H2H_POGASH_ECOM = 201; // H2H платёж ECOM
    public const H2H_ECOM = 202; // H2H оплата товаров и услуг
    public const TRANSFER_B2C_SBP = 203; // Выплата через СБП

    public const OUT_TYPES = [
        self::TOSCHET,
        self::TOCARD,
    ];

    public const AUTO_TYPES = [
        self::AVTOPLATECOM,
        self::AVTOPLATATF,
    ];

    public static function getAll()
    {
        return [
            self::JKH => 'Оплата ЖКХ',
            self::REGCARD => 'Привязка карты',
            self::ECOM => 'Оплата товара/услуги',
            self::POGASHATF => 'Платёж AFT',
            self::TOSCHET => 'Выплата на счет',
            self::AVTOPLATATF => 'Автоплатёж AFT',
            self::TOCARD => 'Выплата на карту',
            self::POGASHECOM => 'Платёж ECOM',
            self::AVTOPLATECOM => 'Автоплатёж ECOM',
            self::VYPLATVOZN => 'Вывод комиссии VEPAY',
            self::VYVODPAYS => 'Вывод средств на р/сч',
            self::REVERSCOMIS => 'Возмещение комисии',
            self::PEREVPAYS => 'Внутренний перевод между счетами',
            self::IDENT => 'Упрощенная идентификация пользователей',
            self::P2P => 'P2P перевод с карты на карту',
            self::JKHPARTS => 'Оплата ЖКХ с разбивкой',
            self::ECOMPARTS => 'Оплата товара/услуги с разбивкой',
            self::POGASHATFPARTS => 'Платёж AFT с разбивкой',
            self::AVTOPLATATFPARTS => 'Автоплатёж AFT с разбивкой',
            self::POGASHECOMPARTS => 'Платёж ECOM с разбивкой',
            self::AVTOPLATECOMPARTS => 'Автоплатёж ECOM с разбивкой',
            self::VYVODPAYSPARTS => 'Перечисление по разбивке',
            self::H2H_POGASH_AFT => 'H2H платёж AFT',
            self::H2H_POGASH_ECOM => 'H2H платёж ECOM',
            self::H2H_ECOM => 'H2H оплата товаров и услуг',
            self::TRANSFER_B2C_SBP => 'Выплата через СБП',
        ];
    }

    /**
     * @return int[]
     */
    public static function getRecurrent()
    {
        return [
            self::AVTOPLATECOM,
            self::AVTOPLATATF,
            self::AVTOPLATECOMPARTS,
            self::AVTOPLATATFPARTS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'uslugatovar_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Id', 'DefaultBankId'], 'integer'],
            [['Name'], 'required'],
            [['Name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'Name' => 'Name',
            'DefaultBankId' => 'Default Bank ID',
        ];
    }
}