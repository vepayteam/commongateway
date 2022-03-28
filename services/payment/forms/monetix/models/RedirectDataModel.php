<?php

namespace app\services\payment\forms\monetix\models;

use app\services\payment\forms\monetix\BaseModel;

class RedirectDataModel extends BaseModel
{
    public $url;
    public $method = 'POST';

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }


}