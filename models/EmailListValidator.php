<?php


namespace app\models;

use yii\validators\Validator;

class EmailListValidator extends Validator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if (!preg_match('/^(([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+),?)*$/ius', $model->$attribute)) {
            $this->addError($model, $attribute, 'Неверный E-mail');
        }
    }
}