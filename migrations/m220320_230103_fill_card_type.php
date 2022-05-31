<?php

use yii\db\Migration;

/**
 * Class m220320_230103_fill_card_type
 */
class m220320_230103_fill_card_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->execute("
            UPDATE cards
            SET CardType = CASE
                WHEN LEFT(CardNumber, 1) = '4' THEN 0 -- VISA
                WHEN LEFT(CardNumber, 2) IN ('51', '52', '53', '54', '55') THEN 1 -- MASTERCARD
                WHEN LEFT(CardNumber, 1) = '2' THEN 2 -- MIR
                WHEN LEFT(CardNumber, 2) IN ('34', '37') THEN 3 -- AMERICAN_EXPRESS
                WHEN LEFT(CardNumber, 2) IN ('31', '35') THEN 4 -- JCB
                WHEN LEFT(CardNumber, 2) IN ('30', '36', '38') THEN 5 -- DINNERSCLUB
                WHEN LEFT(CardNumber, 2) IN ('50', '56', '57', '58', '63', '67') THEN 6 -- MAESTRO
                WHEN LEFT(CardNumber, 2) = '60' THEN 7 -- DISCOVER
                WHEN LEFT(CardNumber, 2) = '62' THEN 8 -- UNIONPAY
                ELSE 0 -- default VISA?
            END
            WHERE CardType = 0;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
    }
}
