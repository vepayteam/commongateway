<?php

namespace app\modules\partner\models\search;

use app\modules\partner\models\PartListFields;
use yii\base\Model;

class PartListFilter extends Model
{
    use PartListFields;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['createdAt', 'withdrawalCreatedAt'], 'date', 'format' => 'php:d.m.Y'],
            [
                [
                    'paySchetId', 'partAmount', 'paySchetAmount', 'clientCompensation', 'partnerCompensation', 'bankCompensation',
                    'withdrawalAmount', 'withdrawalPayschetId',
                ],
                'number',
            ],
            [
                ['partnerName', 'extId', 'message', 'cardNumber', 'cardHolder', 'contract', 'fio'],
                'string', 'max' => 255,
            ],
        ];
    }
}