<?php


namespace app\models\antifraud;


use app\models\antifraud\control_objects\AntiFraudStats;
use app\models\antifraud\control_objects\refund\RefundValidate;
use app\models\antifraud\support_objects\RefundInfo;
use app\models\antifraud\tables\AFFingerPrit;
use yii\helpers\VarDumper;

/**
 * @property RefundInfo $ref_info
*/
class AntiFraudRefund
{
    private $ref_info;

    public function __construct($trans_id, $partner_id, $card_num)
    {
        $finger_id = $this->finger_print_id($trans_id, $partner_id);
        $this->ref_info = new RefundInfo($trans_id, $finger_id, $card_num);
    }

    public function validate(): bool
    {
        $validator_refund = new RefundValidate($this->ref_info);
        $data = $validator_refund->data();
        return $validator_refund->validated($data);
    }

    private function finger_print_id($transaction_id, $partner_id)
    {
        $record = AFFingerPrit::find()
            ->where(['transaction_id'=>$transaction_id, 'user_hash'=>$partner_id])
            ->orderBy('id desc')
            ->one();
        if (!$record){
            $record = new AFFingerPrit();
            $record->user_hash = $partner_id;
            $record->transaction_id = $transaction_id;
            $record->status = 1;
            $record->weight = 0;
            $record->save();
            $record->refresh();
        }
        return $record->id;
    }
}