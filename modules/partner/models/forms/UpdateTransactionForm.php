<?php

namespace app\modules\partner\models\forms;

use app\services\payment\models\PaySchet;
use yii\base\Model;

class UpdateTransactionForm extends Model
{
    /**
     * @var {@see PaySchet::$ID}
     */
    public $id;

    /**
     * @var string|null {@see PaySchet::$Extid}
     */
    public $extId;

    /**
     * @var int {@see PaySchet::$SummPay}
     */
    public $paymentAmount;

    /**
     * @var int {@see PaySchet::$BankComis}
     */
    public $merchantCommission;

    /**
     * @var int {@see PaySchet::$MerchVozn}
     */
    public $providerCommission;

    /**
     * @var int {@see PaySchet::$Status}
     */
    public $status;

    /**
     * @var string|null {@see PaySchet::$ErrorInfo}
     */
    public $description;

    /**
     * @var string|null {@see PaySchet::$ExtBillNumber}
     */
    public $providerId;

    /**
     * @var string|null {@see PaySchet::$Dogovor}
     */
    public $contractNumber;

    /**
     * @var string|null {@see PaySchet::$RCCode}
     */
    public $rcCode;

    /**
     * @var bool отправлять callback
     */
    public $sendCallback = false;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'paymentAmount', 'merchantCommission', 'providerCommission', 'status'], 'required'],
            [['id', 'paymentAmount', 'merchantCommission', 'providerCommission', 'status'], 'integer'],
            [['id'], 'exist', 'targetClass' => PaySchet::class, 'targetAttribute' => 'ID'],
            [['extId', 'description', 'providerId', 'contractNumber', 'rcCode'], 'string'],
            [['sendCallback'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'extId' => 'Ext ID',
            'paymentAmount' => 'Сумма',
            'merchantCommission' => 'Комиссия с мерчанта',
            'providerCommission' => 'Комиссия провайдера',
            'status' => 'Статус',
            'description' => 'Описание статуса',
            'providerId' => 'Provider ID',
            'contractNumber' => 'Номер договора',
            'rcCode' => 'Код ответа провайдера',
            'sendCallback' => 'Отправить Callback',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return UpdateTransactionForm
     */
    public static function mapFromPaySchet(PaySchet $paySchet): UpdateTransactionForm
    {
        $updateTransactionForm = new UpdateTransactionForm();
        $updateTransactionForm->id = $paySchet->ID;
        $updateTransactionForm->extId = $paySchet->Extid;
        $updateTransactionForm->paymentAmount = $paySchet->SummPay;
        $updateTransactionForm->merchantCommission = $paySchet->MerchVozn;
        $updateTransactionForm->providerCommission = $paySchet->BankComis;
        $updateTransactionForm->status = $paySchet->Status;
        $updateTransactionForm->description = $paySchet->ErrorInfo;
        $updateTransactionForm->providerId = $paySchet->ExtBillNumber;
        $updateTransactionForm->contractNumber = $paySchet->Dogovor;
        $updateTransactionForm->rcCode = $paySchet->RCCode;

        return $updateTransactionForm;
    }
}
