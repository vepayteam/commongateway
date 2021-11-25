<?php

namespace app\services\payment\forms;

class MfoCallbackForm extends BaseForm
{
    /**
     * @var string $id
     */
    public $order_id;
    /**
     * @var string $token
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
