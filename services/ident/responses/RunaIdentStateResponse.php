<?php


namespace app\services\ident\responses;


use yii\base\Model;

class RunaIdentStateResponse extends Model
{
    public $details;
    public $tid;
    public $state_code;
    public $state_description;

    public function rules()
    {
        return [
            [['detail', 'tid', 'state_code', 'state_description'], 'safe'],
        ];
    }
}
