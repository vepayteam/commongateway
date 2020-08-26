<?php

use yii\db\Migration;

/**
 * Class m200728_064434_add_postbackurl_to
 */
class m200728_064434_add_postbackurl_to extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'PostbackUrl', $this->string()->after('CancelUrl'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'PostbackUrl');

        return true;
    }
}
