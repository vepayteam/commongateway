<?php

namespace app\services\paymentTransfer;

use app\models\payonline\Partner;
use Carbon\Carbon;
use yii\base\Model;

class TransferRewardForm extends Model
{
    /**
     * Стандартный тип перевода
     */
    public const TYPE_STANDARD = 0;

    /**
     * В старом коде был хак, при котором просто создавалась запись в VyvodSystem, без отправки transferToAccount запроса.
     * Сейчас в лк ещё есть эндпоинты, которые создают фейковую запись.
     */
    public const TYPE_FAKE = 1;

    private const TYPE_LIST = [
        self::TYPE_FAKE,
        self::TYPE_STANDARD,
    ];

    /**
     * @var string
     */
    public $datefrom;

    /**
     * @var string
     */
    public $dateto;

    /**
     * @var string
     */
    public $partner;

    /**
     * @var string
     */
    public $summ;

    /**
     * @var string
     */
    public $type;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['datefrom', 'dateto', 'partner', 'summ', 'type'], 'required'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['partner'], 'exist', 'targetClass' => Partner::class, 'targetAttribute' => 'ID'],
            [['summ'], 'integer', 'min' => 1],
            [['type'], 'integer'],
            [['type'], 'in', 'range' => self::TYPE_LIST],
        ];
    }

    /**
     * @return Carbon
     */
    public function getDateFrom(): Carbon
    {
        return Carbon::createFromFormat('d.m.Y H:i', $this->datefrom);
    }

    /**
     * @return Carbon
     */
    public function getDateTo(): Carbon
    {
        return Carbon::createFromFormat('d.m.Y H:i', $this->dateto);
    }

    /**
     * @return int
     */
    public function getPartner(): int
    {
        return (int)$this->partner;
    }

    /**
     * @return int
     */
    public function getSum(): int
    {
        return (int)$this->summ;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return (int)$this->type;
    }
}