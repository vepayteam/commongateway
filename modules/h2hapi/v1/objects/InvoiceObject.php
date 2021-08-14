<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;
use app\models\payonline\Partner;
use app\modules\h2hapi\v1\services\InvoiceApiService;
use app\modules\h2hapi\v1\services\invoiceApiService\InvoiceCreateException;
use app\services\payment\models\Currency;
use app\services\payment\models\PaySchet;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * REST-объект Счет.
 */
class InvoiceObject extends ApiObject
{
    /**
     * @var Partner
     */
    private $partner;

    /**
     * @var int
     */
    public $id;
    /**
     * @var int Сумма платежа в копейках.
     */
    public $amountFractional;
    /**
     * @var string Валюта платежа (По умолчанию RUB).
     */
    public $currencyCode;
    /**
     * @var string Номер договора.
     */
    public $documentId;
    /**
     * @var string Внешний идентификатор Счета.
     */
    public $externalId;
    /**
     * @var string
     */
    public $description;
    /**
     * @var int Тайм-аут ожидания оплаты в секундах (от 10 до 59 минут).
     */
    public $timeoutSeconds;
    /**
     * @var string URL для возврата после завершения платежа (успех).
     */
    public $successUrl;
    /**
     * @var string URL для возврата после завершения платежа (ошибка).
     */
    public $failUrl;
    /**
     * @var string URL для возврата после отказа от оплаты.
     */
    public $cancelUrl;
    /**
     * @var string
     */
    public $postbackUrl;
    /**
     * @var string
     */
    public $postbackUrlV2;
    /**
     * @var InvoiceClientObject
     */
    public $client;
    /**
     * @var InvoiceStatusObject
     */
    public $status;

    /**
     * {@inheritDoc}
     * @param Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->client = new InvoiceClientObject();
        $this->partner = $partner;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            // Простые поля
            [['amountFractional', 'currencyCode'], 'required'],
            [['documentId', 'externalId'], 'string', 'min' => 1, 'max' => 40],
            [['successUrl', 'failUrl', 'cancelUrl'], 'url'],
            [['successUrl', 'failUrl', 'cancelUrl'], 'string', 'max' => 1000],

            [['description'], 'string', 'max' => 200],
            [['amountFractional'], 'integer', 'min' => 100, 'max' => 1000000 * 100],
            [['timeoutSeconds'], 'integer', 'min' => 10 * 60, 'max' => 59 * 60],
            [
                ['currencyCode'], 'exist',
                'targetClass' => Currency::class, 'targetAttribute' => 'Code',
                'message' => 'Указанная валюта не поддерживается.',
            ],
            [
                ['externalId'], 'unique',
                'targetClass' => PaySchet::class, 'targetAttribute' => 'ExtId',
                'filter' => function (ActiveQuery $query) {
                    if ($this->id !== null) {
                        $query->andWhere(['!=', 'ID', $this->id]);
                    }
                    $query->andWhere(['IdOrg' => $this->partner->ID]);
                }
            ],

            // Вложенные объекты
            [['client'], 'required'],

            // Встроенные валидаторы
            /** @see validateAmountFractional() */
            [['amountFractional'], 'validateAmountFractional'],
        ];
    }

    /**
     * @throws InvoiceCreateException
     * @throws InvalidConfigException
     */
    public function validateAmountFractional()
    {
        if (!$this->hasErrors()) {
            /** @var InvoiceApiService $service */
            $service = \Yii::$app->get(InvoiceApiService::class);
            $uslugatovar = $service->findUslugatovar($this->partner, $this->amountFractional);

            /** @see ValidateTrait::validatePaySchetWithUslugatovar() */
            if ($this->amountFractional < $uslugatovar->MinSumm) {
                $this->addError('amountFractional', "Минимальная сумма платежа: {$uslugatovar->MinSumm} (в копейках/центах).");
            }
            if ($this->amountFractional > $uslugatovar->MaxSumm) {
                $this->addError('amountFractional', "Максимальная сумма платежа: {$uslugatovar->MaxSumm} (в копейках/центах).");
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'id',
            'amountFractional',
            'currencyCode',
            'documentId',
            'externalId',
            'description',
            'timeoutSeconds',
            'successUrl',
            'failUrl',
            'cancelUrl',

            'client',

            'status',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return $this
     */
    public function mapPaySchet(PaySchet $paySchet): InvoiceObject
    {
        $this->id = $paySchet->ID;

        $this->amountFractional = (int)$paySchet->SummPay;
        $this->currencyCode = Currency::findOne($paySchet->currency->Id)->Code;
        $this->documentId = $paySchet->Dogovor;
        $this->externalId = $paySchet->Extid;
        $this->description = $paySchet->QrParams;
        $this->timeoutSeconds = $paySchet->TimeElapsed;
        $this->successUrl = $paySchet->SuccessUrl;
        $this->failUrl = $paySchet->FailedUrl;
        $this->cancelUrl = $paySchet->CancelUrl;

        $this->client = (new InvoiceClientObject())->mapPaySchet($paySchet);
        $this->status = (new InvoiceStatusObject())->mapPaySchet($paySchet);

        return $this;
    }
}