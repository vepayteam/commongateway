<?php

namespace app\services\paymentReport\entities;

use app\services\payment\models\PaySchet;
use yii\base\Model;

class PaymentReportEntity extends Model
{
    /**
     * @var PaymentReportGroup
     */
    public $paymentReportGroup;


    /**
     * @var int {@see PaySchet::$Status}
     */
    public $status;

    /**
     * @var int {@see PaySchet::$SummPay}
     */
    public $totalPaymentAmount;

    /**
     * @var int {@see PaySchet::$ComissSumm}
     */
    public $totalClientCommission;

    /**
     * @var int sum of {@see PaySchet::$BankComis}
     */
    public $totalBankCommission;

    /**
     * @var int sum of {@see PaySchet::$MerchVozn}
     */
    public $totalMerchantReward;

    /**
     * @var int number of transactions
     */
    public $paymentCount;


    /**
     * @var int
     */
    public $totalRewardAmount;

    /**
     * @param PaymentReportEntity $entity
     * @return void
     */
    public function addEntity(PaymentReportEntity $entity)
    {
        $this->totalPaymentAmount += $entity->totalPaymentAmount;
        $this->totalClientCommission += $entity->totalClientCommission;
        $this->totalMerchantReward += $entity->totalMerchantReward;
        $this->totalBankCommission += $entity->totalBankCommission;
        $this->totalRewardAmount += $entity->totalRewardAmount;

        $this->paymentCount += $entity->paymentCount;
    }

    /**
     * @param PaymentReportEntity $entity
     * @return void
     */
    public function subEntity(PaymentReportEntity $entity)
    {
        $this->totalPaymentAmount -= $entity->totalPaymentAmount;
        $this->totalClientCommission -= $entity->totalClientCommission;
        $this->totalMerchantReward -= $entity->totalMerchantReward;
        $this->totalBankCommission -= $entity->totalBankCommission;
        $this->totalRewardAmount -= $entity->totalRewardAmount;

        $this->paymentCount += $entity->paymentCount;
    }

    /**
     * @return int
     */
    private function calculateTotalRewardAmount(): int
    {
        /**
         * Для refund транзакций устанавливаем rewardAmount в 0
         */
        if ((int)$this->status === PaySchet::STATUS_REFUND_DONE) {
            return 0;
        } else {
            return $this->totalClientCommission - $this->totalBankCommission + $this->totalMerchantReward;
        }
    }

    /**
     * @param array $row
     * @return PaymentReportEntity
     */
    public static function fromQuery(array $row): PaymentReportEntity
    {
        $entity = new PaymentReportEntity();

        $entity->paymentReportGroup = PaymentReportGroup::fromQuery($row);

        $entity->status = $row['status'];
        $entity->totalPaymentAmount = $row['totalPaymentAmount'];
        $entity->totalClientCommission = $row['totalClientCommission'];
        $entity->totalMerchantReward = $row['totalMerchantReward'];
        $entity->totalBankCommission = $row['totalBankCommission'];
        $entity->paymentCount = $row['paymentCount'];

        $entity->totalRewardAmount = $entity->calculateTotalRewardAmount();

        return $entity;
    }

    /**
     * Функция возвращает пустой entity с обнуленными суммами для подведения итогов
     *
     * @param PaymentReportGroup $paymentReportGroup
     * @return PaymentReportEntity
     */
    public static function newEmptyEntity(PaymentReportGroup $paymentReportGroup): PaymentReportEntity
    {
        $entity = new PaymentReportEntity();

        $entity->paymentReportGroup = $paymentReportGroup;

        $entity->status = 0;
        $entity->totalPaymentAmount = 0;
        $entity->totalClientCommission = 0;
        $entity->totalMerchantReward = 0;
        $entity->totalBankCommission = 0;
        $entity->paymentCount = 0;

        $entity->totalRewardAmount = 0;

        return $entity;
    }
}
