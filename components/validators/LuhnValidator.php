<?php

namespace app\components\validators;

use app\helpers\Validators;
use yii\validators\Validator;

/**
 * Valudation by Luhn algorithm ({@link https://en.wikipedia.org/wiki/Luhn_algorithm}).
 */
class LuhnValidator extends Validator
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = \Yii::t('app.payment-errors', 'Неверный номер карты');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function validateValue($value): ?array
    {
        if (!is_string($value) || !Validators::checkByLuhnAlgorithm($value)) {
            return [$this->message, []];
        }
        return null;
    }
}