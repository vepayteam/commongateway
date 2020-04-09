<?php


namespace app\models\antifraud\control_objects;

use app\models\antifraud\tables\AFFingerPrit;
use app\models\antifraud\tables\AFStat;
use yii\base\Exception;

class AntiFraudStats
{

    private $trans_id;

    public function __construct(string $transaction_id)
    {
        $this->trans_id = $transaction_id;
    }


    public function write_state(bool $status)
    {
        $rec = AFFingerPrit::find()->where(['transaction_id' => $this->trans_id])->all();
        foreach ($rec as $item) {
            $item->status = $status;
            $item->save();
        }
    }

    public function transaction_info(string $user_hash)
    {
        /**@var AFFingerPrit $record*/
        $record = AFFingerPrit::find()
            ->where([
                'transaction_id' => $this->trans_id,
                'user_hash' => $user_hash
            ])->one();
        return $record;
    }
}