<?php

use yii\db\Migration;

/**
 * Dative signatory for partner.
 */
class m220113_012901_add_signatory_dative_partner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'SignatoryShortDative',
            $this->string(63)->after('PodpOsnovanRod'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'SignatoryShortDative');
    }
}
