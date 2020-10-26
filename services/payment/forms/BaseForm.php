<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use yii\base\Model;

abstract class BaseForm extends Model
{
    /** @var Partner */
    public $partner;

    public function getError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

}
