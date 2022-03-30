<?php

namespace app\services\payment\forms\monetix\models;

use app\services\payment\forms\monetix\BaseModel;

class AcsReturnUrlModel extends BaseModel
{
    public $return_url;
    public $notification_url_3ds;

    public function __construct($url)
    {
        $this->return_url = $url;
        $this->notification_url_3ds = $url;
    }

    public function attributes()
    {
        return [
            'return_url',
            '3ds_notification_url',
        ];
    }

    public function get3ds_notification_url() {
        return $this->notification_url_3ds;
    }
}