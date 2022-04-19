<?php

namespace app\modules\partner\models\forms;

use app\services\payment\models\Bank;
use Carbon\Carbon;
use yii\base\Model;

class AdminSettingsForm extends Model
{
    private const HOLIDAY_LIST_ITEM_FORMAT = 'j.m';

    /**
     * @var string
     */
    public $alarmEmail;
    /**
     * @var string|int
     */
    public $alarmBankNoResponseInterval;
    /**
     * @var string|int
     */
    public $alarmSmsGateNoResponseInterval;
    /**
     * @var string|int
     */
    public $alarmStatusFreezeInterval;
    /**
     * @var string
     */
    public $holidayList;
    /**
     * @var string|int ID of {@see Bank} to make payment through.
     */
    public $bankForPayment;
    /**
     * @var string|int ID of {@see Bank} for transferring to card.
     */
    public $bankForTransferToCard;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'alarmEmail',
                    'alarmBankNoResponseInterval', 'alarmSmsGateNoResponseInterval', 'alarmStatusFreezeInterval',
                ],
                'required',
            ],
            [['alarmEmail'], 'email'],
            [
                ['alarmBankNoResponseInterval'],
                'integer', 'min' => 10,
            ],
            [['holidayList'], 'filter', 'filter' => 'trim'],
            [['holidayList'], 'validateHolidayList'], /** @see validateHolidayList() */
            [
                ['bankForPayment'],
                'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'ID',
                'when' => function (AdminSettingsForm $model) {
                    return (int)$model->bankForPayment !== -1;
                }
            ],
            [
                ['bankForTransferToCard'],
                'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'ID',
                'when' => function (AdminSettingsForm $model) {
                    return (int)$model->bankForTransferToCard !== -1;
                }
            ],
        ];
    }

    public function validateHolidayList()
    {
        $label = $this->getAttributeLabel('holidayList');

        if (!empty($this->holidayList)) {
            $dates = explode(';', $this->holidayList);

            foreach ($dates as $date) {
                if (!Carbon::canBeCreatedFromFormat($date, self::HOLIDAY_LIST_ITEM_FORMAT)) {
                    $this->addError('holidayList', "Неправильный формат поля «{$label}».");
                    break;
                }

                // Disallow inexistent month days like "33.12" (December 33rd)
                $dt = Carbon::createFromFormat(
                    self::HOLIDAY_LIST_ITEM_FORMAT . '.Y',
                    $date . '.2000' // use a leap year to have the February 29th
                );
                if ($dt->format(self::HOLIDAY_LIST_ITEM_FORMAT) !== $date) {
                    $this->addError('holidayList', "Неправильный день месяца в поле «{$label}».");
                    break;
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'alarmEmail' => 'E-mail для оповещений',
            'alarmBankNoResponseInterval' => 'Отсутствие изменений статуса операции и/или отклика на стороне эквайера в интервале между запросом обработки и моментом проверки в течение, минут',
            'alarmSmsGateNoResponseInterval' => 'Отсутствие отклика со стороны SMS шлюза в течение, минут',
            'alarmStatusFreezeInterval' => 'Отсутствие изменений статуса операции в течение, минут',
            'holidayList' => 'Праздничные дни',
            'bankForPayment' => 'Банк для оплаты',
            'bankForTransferToCard' => 'Банк для перечислений на карту',
        ];
    }
}