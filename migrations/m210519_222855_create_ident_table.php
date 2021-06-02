<?php

use app\services\ident\models\Ident;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%ident}}`.
 */
class m210519_222855_create_ident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Ident::tableName(), [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->notNull(),

            'FirstName' => $this->string(),
            'LastName' => $this->string(),
            'Patronymic' => $this->string(),
            'Series' => $this->string(),
            'Number' => $this->string(),
            'Inn' => $this->string(),
            'Snils' => $this->string(),
            'BirthDay' => $this->string(),
            'IssueData' => $this->string(),
            'IssueCode' => $this->string(),
            'Issuer' => $this->string(),

            'BankId' => $this->integer()->defaultValue(0),
            'DateCreated' => $this->integer(),
            'DateUpdated' => $this->integer(),
            'Status' => $this->integer(),
            'Response' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Ident::tableName());
    }
}
