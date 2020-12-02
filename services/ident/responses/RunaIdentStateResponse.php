<?php


namespace app\services\ident\responses;


use yii\base\Model;

class RunaIdentStateResponse extends Model
{
    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    public $details;
    public $tid;
    public $state_code;
    public $state_description;

    public function rules()
    {
        return [
            [['details', 'tid', 'state_code', 'state_description'], 'safe'],
        ];
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        if(!isset($this->state_code)) {
            return self::STATUS_ERROR;
        }

        switch($this->state_code) {
            case '00000':
                return self::STATUS_SUCCESS;
            case '00008':
                return self::STATUS_WAIT;
            default:
                return self::STATUS_ERROR;
        }
    }
}
