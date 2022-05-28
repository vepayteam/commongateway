<?php

use yii\db\Migration;

/**
 * Class m220526_073622_payschet_acs
 */
class m220526_073622_payschet_acs extends Migration
{
    /**
     * @throws \yii\base\Exception
     */
    public function up()
    {
        $this->createTable('pay_schet_acs_redirect', [
            'id' => $this->primaryKey()->unsigned(),
            'status' => $this->tinyInteger()->unsigned()->notNull(),
            'url' => $this->string(512),
            'method' => $this->char(8),
            'postParametersJson' => $this->json(),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('pay_schet_acs_redirect');
    }
}
