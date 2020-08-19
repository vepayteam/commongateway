<?php


namespace app\models\bank\mts_soap;


use yii\base\Model;

class PerfomP2P extends Model
{
    public $orderId;
    public $type;
    public $ip;
    public $toCard;

    public function rules()
    {
        return [
            [['orderId', 'toCard', 'type'], 'required']
        ];
    }

}
