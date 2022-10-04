<?php

namespace app\modules\partner\models\search;

use app\modules\partner\models\data\IdentificationListItem;

class IdentificationListFilter extends IdentificationListItem
{
    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'number'],
            [['createdAt', 'passportIssueDate'], 'date', 'format' => 'php:d.m.Y'],
            [
                [
                    'transactionNumber', 'firstName', 'lastName', 'middleName', 'inn', 'snils', 'passportSeries',
                    'passportNumber', 'passportDepartmentCode', 'passportIssueDate', 'passportIssuedBy',
                ],
                'string', 'max' => 255,
            ],
        ];
    }
}