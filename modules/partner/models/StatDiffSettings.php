<?php

namespace app\modules\partner\models;

use app\services\payment\models\Bank;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;

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

    public static function saveByForm(DiffDataForm $form)
    {
        $settings = StatDiffSettings::find()
            ->where(['BankId' => $form->bank])
            ->one();
        if (!$settings) {
            $settings = new StatDiffSettings();
            $settings->BankId = $form->bank;
        }

        $settings->RegistrySelectColumn = $form->registrySelectColumn;
        $settings->RegistryStatusColumn = $form->registryStatusColumn;
        $settings->AllRegistryStatusSuccess = $form->allRegistryStatusSuccess;
        $settings->DbColumn = $form->dbColumn;
        $settings->Statuses = Json::encode($form->statuses);
        $settings->save();
    }
}
