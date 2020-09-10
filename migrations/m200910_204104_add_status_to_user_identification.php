<?php

use yii\db\Migration;

/**
 * Class m200910_204104_add_status_to_user_identification
 */
class m200910_204104_add_status_to_user_identification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user_identification', 'Status', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user_identification', 'Status');

        return true;
    }

}
