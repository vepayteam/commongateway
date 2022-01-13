<?php

namespace app\services\payment\forms;

class MfoCallbackForm extends BaseForm
{
    /**
     * @var string $order_id
     */
    public $order_id;
    /**
     * @var string $cardToken
     */
    public $cardToken;

    /**
     * @var string $card
     */
    public $card;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['order_id', 'cardToken', 'card'], 'string'],
            [['order_id', 'cardToken', 'card'], 'required'],
        ];
    }

}
