<?php

namespace app\services\payment\banks\bank_adapter_responses;

class CheckStatusPayResponse extends BaseResponse
{
    public $xml;
    public $rrn = '';
    public $transId;

    public $cardNumber;
    public $cardRefId;
    public $expYear;
    public $expMonth;
    public $cardHolder;
    public $operations;

    /**
     * проверка полученного внешнего идентификатора карты
     * @return array[]
     */
    public function rules(): array
    {
        return [
            [['cardRefId'], 'required'],
        ];
    }
}
