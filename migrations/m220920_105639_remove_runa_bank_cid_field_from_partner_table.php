<?php

use yii\db\Migration;

/**
 * Class m220920_105639_remove_runa_bank_cid_field_from_partners_table
 */
class m220920_105639_remove_runa_bank_cid_field_from_partner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('partner', 'RunaBankCid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('partner', 'RunaBankCid', $this->integer());

        return true;
    }
}
