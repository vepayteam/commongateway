<?php

namespace app\modules\partner\models\forms;

use app\services\payment\models\Bank;
use yii\base\Model;

/**
 * Model representing a bank in the settings form.
 */
class AdminSettingsBankForm extends Model
{
    /**
     * @var string|bool Only-read name.
     */
    public $name;

    /**
     * @var string|int
     */
    public $sortOrder;
    /**
     * @var string|int Minimal sum to use AFT gate, in fractional currency units e.g. cents.
     */
    public $aftMinSum;
    /**
     * @var string|bool
     */
    public $usePayIn;
    /**
     * @var string|bool
     */
    public $useApplePay;
    /**
     * @var string|bool
     */
    public $useGooglePay;
    /**
     * @var string|bool
     */
    public $useSamsungPay;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['sortOrder'], 'integer', 'min' => 0, 'max' => 255],
            [['aftMinSum'], 'integer', 'min' => 0],
            [['aftMinSum'], 'default', 'value' => null],
            [
                ['usePayIn', 'useApplePay', 'useGooglePay', 'useSamsungPay'],
                'boolean',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Банк',
            'sortOrder' => 'Приоритет',
            'aftMinSum' => 'Порог для платежа через AFT (в копейках)',
            'usePayIn' => 'Использовать',
            'useApplePay' => 'Apple Pay',
            'useGooglePay' => 'Google Pay',
            'useSamsungPay' => 'Samsung Pay',
        ];
    }

    /**
     * @param Bank $bank
     * @return $this
     */
    public function mapBank(Bank $bank): AdminSettingsBankForm
    {
        $this->name = $bank->Name;
        $this->sortOrder = $bank->SortOrder;
        $this->aftMinSum = $bank->AftMinSum;
        $this->usePayIn = $bank->UsePayIn;
        $this->useApplePay = $bank->UseApplePay;
        $this->useGooglePay = $bank->UseGooglePay;
        $this->useSamsungPay = $bank->UseSamsungPay;

        return $this;
    }
}