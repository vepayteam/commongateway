<?php

namespace app\models\antifraud\control_objects;

use app\models\antifraud\tables\AFFingerPrit;
use yii\base\Model;

class FingerPrint extends Model
{
    public $transaction_id;
    public $hash;

    public function rules()
    {
        return [
            [['transaction_id', 'hash'], 'required'],
            [['hash'], 'string'],
            [['transaction_id'], 'integer']
        ];
    }

    public function saveHash()
    {
        $record = AFFingerPrit::find()
            ->where(['transaction_id' => $this->transaction_id, 'user_hash' => $this->hash])
            ->one();
        if (!$record) {
            $record = new AFFingerPrit();
            $record->user_hash = $this->hash;
            $record->transaction_id = $this->transaction_id;
            $record->status = false;
            $record->weight = 0;
            $record->save();
        }
    }
}