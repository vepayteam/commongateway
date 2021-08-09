<?php

namespace app\modules\hhapi\v1\objects;

use app\components\api\ApiObject;
use app\models\payonline\Cards;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PaySchet;

/**
 * Карта, по которой происходит оплата Счета.
 *
 * @see CreatePayForm
 */
class PaymentCardObject extends ApiObject
{
    /**
     * @var string
     */
    public $number;
    /**
     * @var string
     */
    public $holder;
    /**
     * @var string
     */
    public $expires;
    /**
     * @var int
     */
    public $cvc;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['number', 'holder', 'expires', 'cvc'], 'required'],
            [['number'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            [['holder'], 'match', 'pattern' => '/^[\w\s]{3,80}$/'],
            [['expires'], 'match', 'pattern' => '/^[01]\d{3}$/'],
            [['cvc'], 'match', 'pattern' => '/^\d{3}$/'],

            /** @see validateNumber() */
            [['number'], 'validateNumber'],
            /** @see validateExpires() */
            [['expires'], 'validateExpires'],
        ];
    }

    public function validateNumber()
    {
        if ($this->hasErrors('number')) {
            return;
        }

        // На тестовом контуре проверяем является ли карта тестовой.
        if (\Yii::$app->params['TESTMODE'] === 'Y' && !in_array($this->number, \Yii::$app->params['testCards'])) {
            $this->addError('number', 'На тестовом контуре допускается использовать только тестовые карты');
        }

        // Валидация по алгоритму Луна.
        if (!Cards::CheckValidCard($this->number)) {
            $this->addError('number', 'Неверный номер карты.');
        }
    }

    public function validateExpires()
    {
        if ($this->hasErrors('expires')) {
            return;
        }

        list($month, $year) = array_map('intval', str_split($this->expires, 2));
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('n');
        if (
            $month < 1 || $month > 12
            || $year + 2000 < $currentYear
            || ($year + 2000 == $currentYear && $month < $currentMonth)
            || $year + 2000 > $currentYear + 10
        ) {
            $this->addError('expires', 'Неверный срок действия.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'number',
            'holder',
            'expires',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return $this
     */
    public function mapPaySchet(PaySchet $paySchet): PaymentCardObject
    {
        $this->number = $paySchet->CardNum;
        $this->holder = $paySchet->CardHolder;
        $this->expires = $paySchet->CardExp;

        return $this;
    }
}