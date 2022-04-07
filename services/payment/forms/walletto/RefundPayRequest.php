<?php

namespace app\services\payment\forms\walletto;

use yii\base\Model;

/**
 * Рефанд запрос для walletto
 */
class RefundPayRequest extends Model
{
    /**
     * @var float|null полная сумма возврата (в долларах/рублях)
     */
    public $amount = null;
}
