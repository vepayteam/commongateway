<?php

use app\models\bank\Banks;
use app\models\payonline\Partner;
use yii\db\Migration;

/**
 * Class m200629_115900_add_bank_by_transfer_to_card_parent
 */
class m200629_115900_add_bank_by_transfer_to_card_parent extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Partner::tableName(),'BankForTransferToCardId', $this->integer()->defaultValue(-1));

        $option = new \app\models\Options();
        $option->Name = Banks::BANK_BY_TRANSFER_IN_CARD_OPTION_NAME;
        $option->Value = '2';
        $option->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Partner::tableName(),'BankForTransferToCardId');

        \app\models\Options::deleteAll(['Name' => Banks::BANK_BY_TRANSFER_IN_CARD_OPTION_NAME]);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200629_115900_add_bank_by_transfer_to_card_parent cannot be reverted.\n";

        return false;
    }
    */
}
