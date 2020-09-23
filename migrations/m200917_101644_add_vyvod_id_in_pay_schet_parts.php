<?php

use app\models\PayschetPart;
use yii\db\Migration;

/**
 * Class m200917_101644_add_payschet_id_to_partner_in_pay_schet_parts
 */
class m200917_101644_add_vyvod_id_in_pay_schet_parts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            PayschetPart::tableName(),
            'VyvodId',
            $this->integer()->after('PayschetId')->defaultValue(0)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(PayschetPart::tableName(), 'VyvodId');

        return true;
    }

}
