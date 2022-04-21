<?php

namespace app\modules\partner\services;

use app\models\Options;
use app\models\partner\admin\AlarmsSettings;
use app\modules\partner\models\forms\AdminSettingsBankForm;
use app\modules\partner\models\forms\AdminSettingsForm;
use app\services\payment\models\Bank;
use yii\base\Component;

/**
 * Service for admin settings management.
 */
class AdminSettingsService extends Component
{
    /**
     * Returns a common settings form.
     *
     * @return AdminSettingsForm
     */
    public function createFrom(): AdminSettingsForm
    {
        $settingsForm = new AdminSettingsForm();

        $bankNoResponseAlarm = AlarmsSettings::findOne(['TypeAlarm' => AlarmsSettings::TYPE_BANK_NO_RESPONSE]);
        $settingsForm->alarmEmail = $bankNoResponseAlarm->EmailAlarm;
        $settingsForm->alarmBankNoResponseInterval = $bankNoResponseAlarm->TimeAlarm;
        $settingsForm->alarmSmsGateNoResponseInterval =
            AlarmsSettings::findOne(['TypeAlarm' => AlarmsSettings::TYPE_SMS_GATE_NO_RESPONSE])->TimeAlarm;
        $settingsForm->alarmStatusFreezeInterval =
            AlarmsSettings::findOne(['TypeAlarm' => AlarmsSettings::TYPE_STATUS_FREEZE])->TimeAlarm;

        $settingsForm->holidayList = Options::getOption(Options::NAME_DISABLED_DAY)->Value;
        $settingsForm->bankForPayment = Options::getOption(Options::NAME_BANK_PAYMENT_ID)->Value;
        $settingsForm->bankForTransferToCard = Options::getOption(Options::NAME_BANK_TRANSFER_TO_CARD_ID)->Value;

        return $settingsForm;
    }

    /**
     * @return AdminSettingsBankForm[]
     */
    public function createBankForms(): array
    {
        /** @var Bank[] $banks */
        $banks = Bank::find()
            ->indexBy('ID')
            ->andWhere(['>', 'ID', 1]) // condition from legacy code...
            ->all();

        return array_map(function (Bank $bank) {
            return (new AdminSettingsBankForm())->mapBank($bank);
        }, $banks);
    }

    /**
     * Saves common settings.
     *
     * @param AdminSettingsForm $settingsForm
     */
    public function save(AdminSettingsForm $settingsForm)
    {
        // Save alarms
        $alarm = AlarmsSettings::findOne(['TypeAlarm' => AlarmsSettings::TYPE_BANK_NO_RESPONSE]);
        $alarm->EmailAlarm = $settingsForm->alarmEmail;
        $alarm->TimeAlarm = $settingsForm->alarmBankNoResponseInterval;
        $alarm->save(false);

        $alarm = AlarmsSettings::findOne(['TypeAlarm' => AlarmsSettings::TYPE_SMS_GATE_NO_RESPONSE]);
        $alarm->EmailAlarm = $settingsForm->alarmEmail;
        $alarm->TimeAlarm = $settingsForm->alarmSmsGateNoResponseInterval;
        $alarm->save(false);

        $alarm = AlarmsSettings::findOne(['TypeAlarm' => AlarmsSettings::TYPE_STATUS_FREEZE]);
        $alarm->EmailAlarm = $settingsForm->alarmEmail;
        $alarm->TimeAlarm = $settingsForm->alarmStatusFreezeInterval;
        $alarm->save(false);


        // Save options
        $option = Options::getOption(Options::NAME_DISABLED_DAY);
        $option->Value = $settingsForm->holidayList;
        $option->save(false);

        $option = Options::getOption(Options::NAME_BANK_PAYMENT_ID);
        $option->Value = $settingsForm->bankForPayment;
        $option->save(false);

        $option = Options::getOption(Options::NAME_BANK_TRANSFER_TO_CARD_ID);
        $option->Value = $settingsForm->bankForTransferToCard;
        $option->save(false);
    }

    /**
     * @param AdminSettingsBankForm[] $bankForms Bank forms array indexed by bank ID.
     */
    public function saveBanks(array $bankForms)
    {
        /** @var Bank[] $banks */
        $banks = Bank::find()
            ->andWhere(['in', 'ID', array_keys($bankForms)])
            ->all();

        foreach ($banks as $bank) {
            $bankForm = $bankForms[$bank->ID];
            $bank->SortOrder = (int)$bankForm->sortOrder;
            $bank->AftMinSum = $bankForm->aftMinSum !== null ? (int)$bankForm->aftMinSum : null;
            $bank->UsePayIn = (bool)$bankForm->usePayIn;
            $bank->UseApplePay = (bool)$bankForm->useApplePay;
            $bank->UseGooglePay = (bool)$bankForm->useGooglePay;
            $bank->UseSamsungPay = (bool)$bankForm->useSamsungPay;
            $bank->save(false);
        }
    }
}