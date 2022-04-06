<?php

namespace app\services\paymentReport\entities;

use yii\base\Model;

class PaymentReportGroup extends Model
{
    /**
     * @var int {@see \app\services\payment\models\PaySchet::$IdUsluga}
     */
    public $serviceId;

    /**
     * @var int {@see \app\models\payonline\Uslugatovar::$IsCustom}
     */
    public $serviceType;

    /**
     * @var string {@see \app\models\payonline\Uslugatovar::$NameUsluga}
     */
    public $serviceName;

    /**
     * @var int {@see \app\models\payonline\Uslugatovar::$ProvVoznagPC}
     */
    public $partnerCommission;

    /**
     * @var int {@see \app\models\payonline\Uslugatovar::$ProvVoznagMin}
     */
    public $partnerMinimalFee;

    /**
     * @var int {@see \app\models\payonline\Uslugatovar::$ProvComisPC}
     */
    public $bankCommission;

    /**
     * @var int {@see \app\models\payonline\Uslugatovar::$ProvComisMin}
     */
    public $bankMinimalFee;

    /**
     * @var string {@see \app\services\payment\models\Bank::$Name}
     */
    public $bankName;

    /**
     * @return string
     */
    public function getKey(): string
    {
        $key = join('.', [
            $this->serviceId,
            $this->serviceType,
            $this->serviceName,
            $this->partnerCommission,
            $this->partnerMinimalFee,
            $this->bankCommission,
            $this->bankMinimalFee,
            $this->bankName,
        ]);

        return sha1($key);
    }

    /**
     * @param array $row
     * @return PaymentReportGroup
     */
    public static function fromQuery(array $row): PaymentReportGroup
    {
        $entity = new PaymentReportGroup();
        $entity->serviceId = $row['serviceId'];
        $entity->serviceType = $row['serviceType'];
        $entity->serviceName = $row['serviceName'];
        $entity->partnerCommission = $row['partnerCommission'];
        $entity->partnerMinimalFee = $row['partnerMinimalFee'];
        $entity->bankCommission = $row['bankCommission'];
        $entity->bankMinimalFee = $row['bankMinimalFee'];
        $entity->bankName = $row['bankName'];

        return $entity;
    }
}
