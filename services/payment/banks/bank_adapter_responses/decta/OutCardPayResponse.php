<?php

namespace app\services\payment\banks\bank_adapter_responses\decta;

use app\services\payment\banks\bank_adapter_responses\BaseResponse;

class OutCardPayResponse extends BaseResponse
{
    /**
     * @var string $api_do_url
     */
    public $api_do_url;

    /**
     * @TODO: create class vars instead of data
     * @var array $data
     */
    public $data;

    public $transaction_data;
}
