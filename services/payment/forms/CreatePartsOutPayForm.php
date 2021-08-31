<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use app\models\payonline\Uslugatovar;
use app\services\payment\models\PartnerBankGate;
use yii\base\Model;

class CreatePartsOutPayForm extends Model
{
    /** @var PartnerBankGate */
    public $partnerBankGate;
    /** @var Uslugatovar */
    public $uslugatovar;
    /** @var PartnerBankRekviz */
    public $partnerBankRekviz;

    public $amount;

}
