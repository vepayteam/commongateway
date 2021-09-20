<?php

namespace app\models;

include '../vendor/autoload.php';

use yii\base\Model;

trait ModelHelper
{
    public function loadAndValidate($data, $formName = null, $validate = false, $attributeNamesToValidate = null, $clearValidateErrors = true)
    {
        return parent::load($data, $formName) && $validate ? $this->validate() : true;
    }
}