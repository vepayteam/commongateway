<?php

namespace app\modules\partner\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * @mixin Model
 */
trait PartListFields
{
    /**
     * @see PayschetPart::$Id
     * @var int
     */
    public $id;
    /**
     * @see Partner::$Name
     * @var string
     */
    public $partnerName;
    /**
     * @see PayschetPart::$Amount
     * @var float
     */
    public $partAmount;
    /**
     * @see PaySchet::$ID
     * @var int
     */
    public $paySchetId;
    /**
     * @see PaySchet::$DateCreate
     * @var int
     */
    public $createdAt;
    /**
     * @see PaySchet::$Extid
     * @var string
     */
    public $extId;
    /**
     * @see PaySchet::$SummPay
     * @var int
     */
    public $paySchetAmount;
    /**
     * @see PaySchet::$ComissSumm
     * @var int
     */
    public $clientCompensation;
    /**
     * @see PaySchet::$MerchVozn
     * @var int
     */
    public $partnerCompensation;
    /**
     * @see PaySchet::$BankComis
     * @var int
     */
    public $bankCompensation;
    /**
     * @see PaySchet::$ErrorInfo
     * @var string
     */
    public $message;
    /**
     * @see PaySchet::$CardNum
     * @var int
     */
    public $cardNumber;
    /**
     * @see PaySchet::$CardHolder
     * @var int
     */
    public $cardHolder;
    /**
     * @see PaySchet::$Dogovor
     * @var int
     */
    public $contract;
    /**
     * @see PaySchet::$FIO
     * @var int
     */
    public $fio;
    /**
     * @see VyvodParts::$PayschetId
     * @var int
     */
    public $withdrawalPayschetId;
    /**
     * @see VyvodParts::$Amount
     * @var int
     */
    public $withdrawalAmount;
    /**
     * @see VyvodParts::$DateCreate
     * @var int
     */
    public $withdrawalCreatedAt;

    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => 'ID ',
            'partnerName' => 'Имя партнера',
            'partAmount' => 'Сумма части, руб.',
            'paySchetId' => 'ID счета',
            'createdAt' => 'Дата создания',
            'extId' => 'Extid',
            'paySchetAmount' => 'Сумма платежа, руб.',
            'clientCompensation' => 'Комиссия, руб.',
            'partnerCompensation' => 'Возн. мерчанта, руб.',
            'bankCompensation' => 'Комисс банка, руб.',
            'message' => 'Сообщение',
            'cardNumber' => 'Номер карты',
            'cardHolder' => 'Владелец карты',
            'contract' => 'Договор',
            'fio' => 'ФИО',
            'partnerId' => 'Мерчант',
            'dateFrom' => 'С даты',
            'dateTo' => 'По дату',
            'withdrawalPayschetId' => 'ID платежа вывода',
            'withdrawalAmount' => 'Сумма платежа вывода',
            'withdrawalCreatedAt' => 'Дата вывода',
        ]);
    }
}