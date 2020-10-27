<?php

use app\services\payment\models\PartnerBankGate;
use yii\db\Migration;

/**
 * Class m201027_090217_add_adv_fields_in_partner_bank_gates
 */
class m201027_090217_add_adv_fields_in_partner_bank_gates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        for($i = 1; $i <= 4; $i++) {
            $this->addColumn(
                PartnerBankGate::tableName(),
                'AdvParam_' . $i,
                $this->text()
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        for($i = 1; $i <= 4; $i++) {
            $this->dropColumn(PartnerBankGate::tableName(), 'AdvParam_' . $i);
        }

        return true;
    }
}
