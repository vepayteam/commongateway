<?php

namespace app\services\payment\forms\monetix\models;

use app\services\payment\forms\monetix\BaseModel;

class GeneralModel extends BaseModel
{
    /** @var int */
    public $project_id;
    /** @var string */
    public $payment_id;
    /** @var string */
    public $terminal_callback_url;
    /** @var string */
    public $referrer_url;
    /** @var string */
    public $merchant_callback_url;
    /** @var string */
    public $signature;

    /**
     * @param int $project_id
     * @param string $payment_id
     */
    public function __construct(int $project_id, string $payment_id)
    {
        $this->project_id = $project_id;
        $this->payment_id = $payment_id;
    }
}