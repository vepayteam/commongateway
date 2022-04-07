<?php

namespace app\modules\mfo\models;

use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\services\payment\exceptions\NotUniquePayException;
use app\services\payment\models\PaySchet;
use app\services\RecurrentPaymentPartsService;
use app\services\recurrentPaymentPartsService\dataObjects\PartData;
use app\services\recurrentPaymentPartsService\dataObjects\PaymentData;
use app\services\recurrentPaymentPartsService\PaymentException;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Рекурентный платеж с разбивкой.
 */
class RecurrentPaymentPartsForm extends Model implements PaymentData
{
    /** Максимальная общая сумма в дробных частях (fractional unit) валюты: копейках, центах. */
    private const MAX_TOTAL_AMOUNT_FRACTIONAL = 1000000 * 100;

    /** @var Partner */
    private $partner;

    /**
     * @var int Идентификатор карты.
     */
    public $card;
    /**
     * @var string Внешний идентификатор запроса.
     */
    public $extid;
    /**
     * @var string ФИО клиента. maxLength: 80.
     */
    public $fullname;
    /**
     * @var string Номер договора. maxLength: 40.
     */
    public $document_id;
    /**
     * @var string Описание.
     */
    public $descript;
    /**
     * Части оплаты - массив вида:
     * ```
     * [
     *      [
     *          'merchant_id' => 123, // ID Мерчанта (см. Partner)
     *          'amount' => 1000, // Сумма перевода в рублях/долларах (целых частях валюты)
     *      ],
     *      ...
     * ];
     * ```
     * @var array
     */
    public $parts;

    public function __construct(Partner $partner)
    {
        parent::__construct();

        $this->partner = $partner;
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['card', 'parts'], 'required'],
            [['document_id', 'extid'], 'string', 'max' => 40],
            [['fullname'], 'string', 'max' => 80],
            [['card'], 'integer'],
            [['descript'], 'string', 'max' => 200],
            [
                ['card'], 'exist',
                'targetClass' => Cards::class, 'targetAttribute' => 'ID',
                'filter' => function (ActiveQuery $query) {
                    $query
                        ->joinWith('user userAlias')
                        ->andWhere(['userAlias.ExtOrg' => $this->partner->ID]);
                },
            ],
            [['extid'], 'validateExtId'], /** @see validateExtId() */
            [['parts'], 'validateParts'], /** @see validateParts() */
        ];
    }

    /**
     * Проверка на уникальность extid у текущего партнера.
     * При ошибке требуется кастомный ответ см VPBC-1345 поэтому вызывается {@see NotUniquePayException}
     *
     * @return void
     * @throws NotUniquePayException
     */
    public function validateExtId()
    {
        /** @var PaySchet $paySchet */
        $paySchet = PaySchet::find()
            ->andWhere(['Extid' => $this->extid])
            ->andWhere(['IdOrg' => $this->partner->ID])
            ->one();

        if ($paySchet) {
            throw new NotUniquePayException($paySchet->ID, $paySchet->Extid);
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws PaymentException
     */
    public function validateParts()
    {
        if (!is_array($this->parts)) {
            $this->addError('parts', 'Части платежа невалидны.');
            return;
        }

        // Валидируем каждую часть
        foreach ($this->parts as $part) {
            $attributes = ArrayHelper::merge([
                'merchant_id' => null,
                'amount' => null,
            ], $part);
            $rules = [
                [['merchant_id', 'amount'], 'required'],
                [['merchant_id'], 'exist', 'targetClass' => Partner::class, 'targetAttribute' => 'ID'],
                [['amount'], 'number', 'min' => 1],
                [['amount'], 'match', 'pattern' => '/^[0-9]+(\.[0-9]{0,2})?$/'],
            ];
            $model = DynamicModel::validateData($attributes, $rules);
            if ($model->hasErrors()) {
                $this->addError('parts', array_values($model->getFirstErrors())[0]);
                return;
            }
        }


        /**
         * Проверка общей суммы. Сравнения производятся в копейках.
         */
        // Проверка по максимальному значению
        $totalAmount = $this->getTotalAmountFractional();
        if ($totalAmount > self::MAX_TOTAL_AMOUNT_FRACTIONAL) {
            $this->addError('parts', 'Общая сумма должна быть меньше ' . (self::MAX_TOTAL_AMOUNT_FRACTIONAL / 100) . ' руб.');
            return;
        }

        // Проверка по услуге
        /** @var RecurrentPaymentPartsService $service */
        $service = \Yii::$app->get(RecurrentPaymentPartsService::class);
        $uslugatovar = $service->findUslugatovar($this->partner);
        if ($totalAmount < $uslugatovar->MinSumm) {
            $this->addError('parts', 'Минимальная общая сумма платежа: ' . $uslugatovar->MinSumm / 100 . ' руб.');
        }
        if ($totalAmount > $uslugatovar->MaxSumm) {
            $this->addError('parts', 'Максимальная общая сумма платежа: ' . $uslugatovar->MaxSumm / 100 . ' руб.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalAmountFractional(): int
    {
        $totalAmountMain = array_sum(array_column($this->parts, 'amount'));
        return $totalAmountMain * 100;
    }

    /**
     * {@inheritDoc}
     */
    public function getCardId(): int
    {
        return (int)$this->card;
    }

    /**
     * {@inheritDoc}
     */
    public function getExternalId(): string
    {
        return (string)$this->extid;
    }

    /**
     * {@inheritDoc}
     */
    public function getDoumentId(): string
    {
        return (string)$this->document_id;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return (string)$this->descript;
    }

    /**
     * {@inheritDoc}
     */
    public function getFullname(): string
    {
        return (string)$this->fullname;
    }

    /**
     * {@inheritDoc}
     */
    public function getParts(): array
    {
        $result = [];
        foreach ($this->parts as $part) {
            $result[] = new PartData($part['merchant_id'], $part['amount'] * 100);
        }
        return $result;
    }
}