<?php

namespace app\services\payment\forms\monetix\models;

use app\services\payment\forms\monetix\BaseModel;

class ReturnUrlModel extends BaseModel
{
    public $success;
    public $decline;
    public $return;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->success = $url;
        $this->decline = $url;
        $this->return = $url;
    }


}