<?php

use app\models\payonline\Partner;
use yii\db\Migration;

/**
 * Class m201207_125738_add_runa_bank_cid_on_partners
 */
class m201207_125738_add_runa_bank_cid_on_partners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            Partner::tableName(),
            'RunaBankCid',
            $this->integer()->after('IsMfo')
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Partner::tableName(), 'RunaBankCid');

        return true;
    }

}
