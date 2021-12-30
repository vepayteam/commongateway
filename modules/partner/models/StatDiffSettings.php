<?php

namespace app\modules\partner\models;

use app\services\payment\models\Bank;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "partner_callback_settings".
 *
 * @property int $Id
 * @property int $BankId
 * @property int $RegistrySelectColumn
 * @property int $RegistryStatusColumn
 * @property bool $AllRegistryStatusSuccess
 * @property string $DbColumn
 * @property string $Statuses
 *
 * @property-read Bank $bank
 */
class StatDiffSettings extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'stat_diff_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['Id', 'BankId', 'RegistrySelectColumn', 'RegistryStatusColumn'], 'integer'],
            [['AllRegistryStatusSuccess'], 'boolean'],
            [['DbColumn', 'Statuses'], 'string'],
        ];
    }

    public function getBank(): ActiveQuery
    {
        return $this->hasOne(Bank::class, ['ID' => 'BankId']);
    }
}
