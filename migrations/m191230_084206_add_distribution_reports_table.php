<?php

use yii\db\Migration;

/**
 * Class m191230_084206_add_distribution_reports_table
 */
class m191230_084206_add_distribution_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('distribution_reports', [
            'id'=>$this->primaryKey(),
            'partner_id'=> $this->integer(),
            'payment'=>$this->tinyInteger(),
            'repayment'=>$this->tinyInteger(),
            'email'=>$this->string(),
            'last_send'=>$this->integer()
        ]);
        $this->createIndex('partner_id', 'distribution_reports', 'partner_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('distribution_reports');
    }
}
