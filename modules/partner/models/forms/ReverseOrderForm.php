<?php

namespace app\modules\partner\models\forms;

use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PaySchet;
use yii\base\Model;
use yii\db\ActiveQuery;

class ReverseOrderForm extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * Сумма в копейках/центах {@see \app\services\payment\helpers\PaymentHelper::convertToPenny()}
     *
     * @var int
     */
    public $refundSum;

    /**
     * @var int
     */
    public $idOrg;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'refundSum'], 'required'],
            ['id', 'integer'],
            ['id', 'exist', 'targetClass' => PaySchet::class, 'targetAttribute' => 'ID', 'filter' => function (ActiveQuery $query) {
                if ($this->idOrg) {
                    $query->andWhere(['IdOrg' => $this->idOrg]);
                }
            }],
            ['refundSum', 'number', 'min' => 1],
            ['refundSum', 'filter', 'filter' => static function ($refundSum) {
                return PaymentHelper::convertToPenny($refundSum);
            }],
            ['refundSum', 'validateRefundSum'],
        ];
    }

    public function validateRefundSum()
    {
        $paySchet = PaySchet::findOne(['ID' => $this->id]);
        $refundedAmount = $paySchet->refundedAmount;
        if (($this->refundSum + $refundedAmount) > $paySchet->getSummFull()) {
            $maxRefundAmount = PaymentHelper::convertToFullAmount($paySchet->getSummFull() - $refundedAmount);
            $this->addError('refundSum', 'Максимальная сумма возврата: ' . $maxRefundAmount);
        }
    }
}
