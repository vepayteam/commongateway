<?php

namespace app\services\payment\banks\bank_adapter_responses;

class CheckStatusPayResponse extends BaseResponse
{
    public $xml;
    public $rrn = '';

    public $cardNumber;
    public $cardRefId;
    public $expYear;
    public $expMonth;
    public $cardHolder;

    public function rules(): array
    {
        return [
            [['cardNumber', 'cardRefId'], 'required'],
            [['expYear', 'expMonth'], 'required'],
            [['cardHolder'], 'required'],
        ];
    }

    /**
     * Проверка получения карточных данных
     * @return bool
     */
    public function hasCardsData(): bool
    {
        return $this->validate();
    }
}
