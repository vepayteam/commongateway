<?php

namespace app\services\paymentReport\forms;

use app\models\partner\stat\PayShetStat;
use yii\base\Model;

class PaymentReportForm extends Model
{
    /**
     * @var int
     */
    public $partnerId;

    /**
     * @var string
     */
    public $dateFrom;

    /**
     * @var string
     */
    public $dateTo;

    /**
     * @var array
     */
    public $serviceIds;

    /**
     * @var array
     */
    public $serviceTypes;

    /**
     * @var array
     */
    public $bankIds;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['partnerId'], 'integer'],
            [['dateFrom', 'dateTo'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['dateFrom', 'dateTo'], 'required'],
            [['serviceIds', 'serviceTypes', 'bankIds'], 'each', 'rule' => ['integer']],
        ];
    }

    public static function fromPayShetStat(PayShetStat $payShetStat): PaymentReportForm
    {
        $form = new PaymentReportForm();
        $form->partnerId = $payShetStat->IdPart;
        $form->dateFrom = $payShetStat->datefrom;
        $form->dateTo = $payShetStat->dateto;
        $form->serviceIds = $payShetStat->usluga;
        $form->serviceTypes = $payShetStat->TypeUslug;
        $form->bankIds = $payShetStat->idBank;

        return $form;
    }
}
