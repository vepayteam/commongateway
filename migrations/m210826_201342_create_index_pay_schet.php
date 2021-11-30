<?php

use yii\db\Migration;

/**
 * Class m210826_201342_create_index_pay_schet
 */
class m210826_201342_create_index_pay_schet extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('pay_schet_idkard_idusluga_datecreate_index', 'pay_schet', ['idkard', 'idusluga', 'datecreate']);
        $this->createIndex('pay_schet_idkard_idusluga_status_index', 'pay_schet', ['idkard', 'idusluga', 'status']);
        $this->createIndex('pay_schet_idkard_idusluga_index', 'pay_schet', ['idkard', 'idusluga']);
        $this->createIndex('pay_schet_status_datecreate_index', 'pay_schet', ['status', 'datecreate']);
        $this->createIndex('pay_schet_idusluga_datecreate_index', 'pay_schet', ['idusluga', 'datecreate']);
        $this->createIndex('pay_schet_idkard_datecreate_index', 'pay_schet', ['idkard', 'datecreate']);
        $this->createIndex('pay_schet_idusluga_idkard_datecreate_status_index', 'pay_schet', ['idusluga', 'idkard', 'datecreate', 'status']);

        $this->createIndex('cards_TypeCard_ExtCardIDP_DateAdd_index', 'cards',  ['TypeCard', 'ExtCardIDP', 'DateAdd']);
        $this->createIndex('cards_TypeCard_ExtCardIDP_index', 'cards',  ['TypeCard', 'ExtCardIDP']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('pay_schet_idkard_idusluga_datecreate_index', 'pay_schet');
        $this->dropIndex('pay_schet_idkard_idusluga_status_index', 'pay_schet');
        $this->dropIndex('pay_schet_idkard_idusluga_index', 'pay_schet');
        $this->dropIndex('pay_schet_status_datecreate_index', 'pay_schet');
        $this->dropIndex('pay_schet_idusluga_datecreate_index', 'pay_schet');
        $this->dropIndex('pay_schet_idkard_datecreate_index', 'pay_schet');
        $this->dropIndex('pay_schet_idusluga_idkard_datecreate_status_index', 'pay_schet');
        $this->dropIndex('cards_ExtCardIDP_index', 'cards',  'ExtCardIDP');

        $this->dropIndex('cards_TypeCard_ExtCardIDP_DateAdd_index', 'cards');
        $this->dropIndex('cards_TypeCard_ExtCardIDP_index', 'cards');


    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210826_201342_create_index_pay_schet cannot be reverted.\n";

        return false;
    }
    */
}