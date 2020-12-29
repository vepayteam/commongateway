<?php

use yii\db\Migration;

/**
 * Class m201228_062844_create_pay_schet_update_log
 */
class m201228_062844_create_pay_schet_update_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pay_schet_log', [
            'Id' => $this->primaryKey(),
            'DateCreate' => $this->integer()->notNull(),
            'PaySchetId' => $this->integer()->notNull(),
            'Status' => $this->integer(),
            'ErrorInfo' => $this->string(),
        ]);

        $this->createIndex(
            'idx-pay_schet-PaySchetId',
            'pay_schet_log',
            'PaySchetId'
        );

        $qTrigger = "CREATE TRIGGER trigger_pay_schet_after_update
                AFTER UPDATE
                ON pay_schet FOR EACH ROW
            BEGIN
                IF old.STATUS <> new.Status OR old.ErrorInfo <> new.ErrorInfo THEN
                        INSERT INTO `pay_schet_log`(`DateCreate`, `PaySchetId`, `Status`, `ErrorInfo`) VALUES (UNIX_TIMESTAMP(NOW()), new.ID, new.Status, new.ErrorInfo);
                END IF;
            END";

        $this->execute($qTrigger);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-pay_schet-PaySchetId',
            'pay_schet_log'
        );

        $this->execute('DROP TRIGGER IF EXISTS `trigger_pay_schet_after_update`');
        if ($this->db->getTableSchema('pay_schet_log', true) !== null) {
            $this->dropTable('pay_schet_log');
        }

        return true;
    }
}
