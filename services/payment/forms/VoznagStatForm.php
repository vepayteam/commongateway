<?php


namespace app\services\payment\forms;

use yii\base\Model;

/**
 * Class VoznagStatForm
 * @package app\services\payment\forms
 */
class VoznagStatForm extends Model
{
    /** @var int Отчет */
    const TYPE_REPORT = 0;
    /** @var int История перечислений на р/с */
    const TYPE_HISTORY_TRANSFER_CALC_ACCT = 1;
    /** @var int История перечислений на счет выдачи */
    const TYPE_HISTORY_TRANSFER_OUT_ACCT = 2;
    /** @var int История вывода вознаграждения */
    const TYPE_HISTORY_OUTPUT_REWARD = 3;

    /**
     * @return string[]
     */
    public static function getDropDownTypes(): array
    {
        return [
            self::TYPE_REPORT => 'Отчет',
            self::TYPE_HISTORY_TRANSFER_CALC_ACCT => 'История перечислений на р/с',
            self::TYPE_HISTORY_TRANSFER_OUT_ACCT => 'История перечислений на счет выдачи',
            self::TYPE_HISTORY_OUTPUT_REWARD => 'История вывода вознаграждения',
        ];
    }

}
