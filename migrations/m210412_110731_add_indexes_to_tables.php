<?php

use yii\db\Migration;

/**
 * Class m210412_110731_add_indexes_to_tables
 */
class m210412_110731_add_indexes_to_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 36 задача
        $this->createIndex('idx_pay_schet_Status', 'pay_schet', 'Status');
        $this->createIndex('idx_pay_schet_Status_DateCreate', 'pay_schet', ['Status', 'DateCreate']);
        $this->createIndex('idx_uslugatovar_IDPartner_IsDeleted_IsCustom', 'uslugatovar', ['IDPartner', 'IsDeleted', 'IsCustom']);
        // 37 задача
        $this->createIndex('idx_cards_TypeCard', 'cards', 'TypeCard');
        // 40 задача
        $this->createIndex('idx_pay_schet_Bank', 'pay_schet', 'Bank');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 36 задача
        $this->dropIndex('idx_pay_schet_Status', 'pay_schet');
        $this->dropIndex('idx_pay_schet_Status_DateCreate', 'pay_schet');
        $this->dropIndex('idx_uslugatovar_IDPartner_IsDeleted_IsCustom', 'uslugatovar');
        // 37 задача
        $this->dropIndex('idx_cards_TypeCard', 'cards');
        // 40 задача
        $this->dropIndex('idx_pay_schet_Bank', 'pay_schet');
    }
}
