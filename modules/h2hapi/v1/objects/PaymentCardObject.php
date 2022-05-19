<?php

namespace app\modules\h2hapi\v1\objects;

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
    public $cardNumber;
    /**
     * @var string
     */
    public $cardHolder;
    /**
     * @var string
     */
    public $expires;
    /**
     * @var string
     */
    public $cvc;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['cardNumber', 'cardHolder', 'expires', 'cvc'], 'required'],
            [['cardNumber'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            [['cardHolder'], 'match', 'pattern' => '/^[\w\s]{3,80}$/'],
            [['expires'], 'match', 'pattern' => '/^[01]\d{3}$/'],
            [['cvc'], 'match', 'pattern' => '/^\d{3}$/'],

            /** @see validateCardNumber() */
            [['cardNumber'], 'validateCardNumber'],
            /** @see validateExpires() */
            [['expires'], 'validateExpires'],
        ];
    }

    public function validateCardNumber()
    {
        if ($this->hasErrors('cardNumber')) {
            return;
        }

        // На тестовом контуре проверяем является ли карта тестовой.
        if (\Yii::$app->params['TESTMODE'] === 'Y' && !in_array($this->cardNumber, \Yii::$app->params['testCards'])) {
            $this->addError('cardNumber', 'На тестовом контуре допускается использовать только тестовые карты');
        }

        // Валидация по алгоритму Луна.
        if (!Cards::CheckValidCard($this->cardNumber)) {
            $this->addError('cardNumber', 'Неверный номер карты.');
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
            'cardNumber',
            'cardHolder',
            'expires',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return $this
     */
    public function mapPaySchet(PaySchet $paySchet): PaymentCardObject
    {
        $this->cardNumber = (string)$paySchet->CardNum;
        $this->cardHolder = (string)$paySchet->CardHolder;
        $this->expires = str_pad((string)$paySchet->CardExp, 4, '0', STR_PAD_LEFT);

        return $this;
    }
}