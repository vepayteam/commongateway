<?php

use yii\db\Migration;

/**
 * Class m201026_113334_add_postbackurl_v2_in_pay_schet
 */
class m201026_113334_add_postbackurl_v2_in_pay_schet extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            \app\services\payment\models\PaySchet::tableName(),
            'PostbackUrl_v2',
            $this->string()->after('PostbackUrl')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\app\services\payment\models\PaySchet::tableName(), 'PostbackUrl_v2');

        return true;
    }
}
