<?php


namespace app\services\payment\banks\bank_adapter_responses;


use app\services\payment\models\PaySchet;
use yii\base\Model;

abstract class BaseResponse extends Model
{
    const STATUS_CREATED = PaySchet::STATUS_WAITING;
    const STATUS_DONE = PaySchet::STATUS_DONE;
    const STATUS_ERROR = PaySchet::STATUS_ERROR;
    const STATUS_CANCEL = PaySchet::STATUS_CANCEL;
    const STATUS_REFUND_DONE = PaySchet::STATUS_REFUND_DONE;
    const STATUS_REVERSE_DONE = PaySchet::STATUS_CANCEL;

    const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_DONE,
        self::STATUS_ERROR,
        self::STATUS_CANCEL,
        self::STATUS_REFUND_DONE,
        self::STATUS_REVERSE_DONE,
    ];

    /** @var number */
    public $status;
    /** @var string */
    public $message;

    public function rules()
    {
        return [
            [['status', 'message'], 'required'],
            ['status', 'validateCheckStatus'],
            ['message', 'string'],
        ];
    }

    public function validateCheckStatus()
    {
        if(!in_array($this->status, self::STATUSES)) {
            $this->addError('status', 'Неизвестный статус');
        }
    }

}
