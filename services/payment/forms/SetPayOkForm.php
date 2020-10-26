<?php


namespace app\services\payment\forms;


use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class SetPayOkForm extends Model
{
    /** @var PaySchet */
    public $paySchet;

    public $approvalCode = '';
    public $rrn = '';
    public $rcCode = '';
    public $message = '';

    public function rules()
    {
        return [
            [['approvalCode', 'rrn', 'rcCode', 'message'], 'string']
        ];
    }

    /**
     * @param CheckStatusPayResponse $checkStatusPayResponse
     * @return bool
     */
    public function loadByCheckStatusPayResponse(CheckStatusPayResponse $checkStatusPayResponse)
    {
        $this->rrn = $checkStatusPayResponse->xml['orderadditionalinfo']['rrn'] ?? '';
        $this->message = $checkStatusPayResponse->xml['orderinfo']['statedescription'] ?? '';
        $this->rcCode = $checkStatusPayResponse->xml['orderadditionalinfo']['rc'] ?? '';
        return true;
    }

}
