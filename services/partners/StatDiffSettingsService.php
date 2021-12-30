<?php

namespace app\services\partners;

use app\modules\partner\models\forms\DiffDataForm;
use app\modules\partner\models\StatDiffSettings;
use yii\helpers\Json;

class StatDiffSettingsService
{
    /**
     * @param int $bankId
     * @return StatDiffSettings|null
     */
    public function getByBankId(int $bankId): ?StatDiffSettings
    {
        /** @var StatDiffSettings $statDiffSettings */
        $statDiffSettings = StatDiffSettings::find()
            ->where(['BankId' => $bankId])
            ->one();

        return $statDiffSettings;
    }

    public function saveByForm(DiffDataForm $form)
    {
        $settings = $this->getByBankId($form->bank);
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
