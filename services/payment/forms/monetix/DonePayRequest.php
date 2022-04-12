<?php

namespace app\services\payment\forms\monetix;

use app\services\payment\forms\monetix\models\GeneralModel;

class DonePayRequest extends BaseModel
{
    /** @var GeneralModel */
    public $general;
    /** @var string */
    public $pares;
}