<?php

use app\services\ident\models\IdentRuna;
use yii\db\Migration;

/**
 * Class m201118_080923_create_ident_runa
 */
class m201118_080923_create_ident_runa extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(IdentRuna::tableName(), [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->notNull(),
            'Tid' => $this->integer()->notNull(),
            'Data' => $this->text(),
            'DateCreate' => $this->integer(),
        ]);

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(IdentRuna::tableName());
        return true;
    }
}
