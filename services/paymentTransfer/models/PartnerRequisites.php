<?php

namespace app\services\paymentTransfer\models;

use yii\base\Model;

class PartnerRequisites extends Model
{
    /**
     * @var string
     */
    public $bic;

    /**
     * @var string
     */
    public $inn;

    /**
     * @var string
     */
    public $kpp;

    /**
     * @var string
     */
    public $settlementAccount;

    /**
     * @var string
     */
    public $correspondentAccount;

    /**
     * @var string
     */
    public $recipientName;

    /**
     * @var string
     */
    public $bankCity;

    /**
     * @var string
     */
    public $bankName;
}