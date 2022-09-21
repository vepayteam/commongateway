<?php

namespace app\modules\partner\models\forms;

use app\models\payonline\Partner;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class BasicPartnerStatisticForm extends Model
{
    const SCENARIO_ADMIN = 'admin';
    const SCENARIO_PARTNER = 'partner';

    /**
     * @var int|string
     */
    public $partnerId;
    /**
     * @var string
     */
    public $dateFrom;
    /**
     * @var string
     */
    public $dateTo;

    /**
     * {@inheritDoc}
     */
    public function scenarios(): array
    {
        $default = parent::scenarios()[static::SCENARIO_DEFAULT];
        $scenarios = [
            static::SCENARIO_ADMIN => $default,
            static::SCENARIO_PARTNER => $default,
        ];
        // disable the "partnerId" prop for partners
        unset($scenarios[static::SCENARIO_PARTNER]['partnerId']);

        return $scenarios;
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['partnerId', 'dateFrom', 'dateTo'], 'required'],
            [['partnerId'], 'exist', 'targetClass' => Partner::class, 'targetAttribute' => 'ID'],
            [['dateFrom', 'dateTo'], 'date', 'format' => 'php:d.m.Y'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'partnerId' => 'Мерчант',
            'dateFrom' => 'С даты',
            'dateTo' => 'По дату',
        ]);
    }
}