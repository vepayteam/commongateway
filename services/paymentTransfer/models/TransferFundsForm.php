<?php

namespace app\services\paymentTransfer\models;

use app\models\payonline\Partner;
use app\services\payment\models\Bank;
use app\services\payment\models\UslugatovarType;
use yii\base\Model;

class TransferFundsForm extends Model
{
    /**
     * Услуга "Внутренний перевод между счетами" {@see UslugatovarType::PEREVPAYS} устарела, вместо неё используется услуга "Вывод средств на р/сч" {@see UslugatovarType::VYVODPAYS}
     */
    public const TYPE_INTERNAL = 0;

    /**
     * Услуга "Вывод средств на р/сч" {@see UslugatovarType::VYVODPAYS}
     */
    public const TYPE_EXTERNAL = 1;

    /**
     * @var string
     */
    public $TypeSchet;

    /**
     * @var string
     */
    public $IdPartner;

    /**
     * @var string
     */
    public $BankId;

    /**
     * @var string
     */
    public $Summ;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['IdPartner', 'BankId', 'Summ'], 'required'],
            [['TypeSchet'], 'integer'],
            [['BankId'], 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'ID'],
            [['IdPartner'], 'exist', 'targetClass' => Partner::class, 'targetAttribute' => 'ID'],
            [['Summ'], 'number', 'min' => 1],
        ];
    }

    /**
     * @return int
     */
    public function getTypeSchet(): int
    {
        return (int)$this->TypeSchet;
    }

    /**
     * @return int
     */
    public function getIdPartner(): int
    {
        return (int)$this->IdPartner;
    }

    /**
     * @return int
     */
    public function getBankId(): int
    {
        return (int)$this->BankId;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return (float)$this->Summ;
    }
}