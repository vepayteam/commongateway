<?php


namespace app\models\antifraud;

use app\models\antifraud\control_objects\AntiFraudStats;
use app\models\antifraud\control_objects\IAntiFraud;
use app\models\antifraud\control_objects\PaymentValidate;

class AntiFraud
{
    private $trans_id;

    public function __construct($trans_id)
    {
        $this->trans_id = $trans_id;
    }

    public function validate($user_hash, $card_num)
    {
        $anti_fraud = new PaymentValidate($this->trans_id, $user_hash, $card_num);
        $data = $anti_fraud->data();
        return $anti_fraud->validated($data);
    }

    public function update_status_transaction($status)
    {
        $state = new AntiFraudStats($this->trans_id);
        $state->write_state($status);
    }
}