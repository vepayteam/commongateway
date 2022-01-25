<?php

namespace app\services\payment\forms\monetix\models;

use app\services\payment\forms\monetix\BaseModel;

class CustomerModel extends BaseModel
{
    /** @var string */
    public $id;
    /** @var string */
    public $ip_address;

    /**
     * @param string $ip_address
     */
    public function __construct(string $id, string $ip_address)
    {
        $this->id = $id;
        $this->ip_address = $ip_address;
    }

    public function rules()
    {
        return [
            [['ip_address', 'id'], 'required'],
            [['ip_address', 'id'], 'string'],
        ];
    }
}