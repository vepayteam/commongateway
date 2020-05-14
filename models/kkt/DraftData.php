<?php


namespace app\models\kkt;


class DraftData
{
    public $type = 1;
    public $customerContact;
    public $quantity = 1.000;
    public $price;
    public $comis;
    public $tax = 6; //НДС не облагается
    public $text;

    public function toArray()
    {
        return [
            'id' => 0,
            'inn' => 0,
            'group' => 1,
            'content' => [
                "type"=> 1,
                "positions" => [[
                    "quantity" => $this->quantity,
                    "price"=> $this->price,
                    "tax"=> $this->tax,
                    "text"=> $this->text,
                    "paymentMethodType" => 4, //Полный расчет
                    "paymentSubjectType" => 10 //Платеж
                ]],
                "checkClose" => [
                  "payments"=> [[
                      "type"=> 2, //сумма по чеку безналичными
                      "amount"=> $this->price
                  ]],
                  "taxationSystem"=> 1
                ],
                'customerContact' => $this->customerContact
            ]
        ];
    }
}