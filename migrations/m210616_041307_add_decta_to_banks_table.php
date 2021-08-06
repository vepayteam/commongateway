<?php

use yii\db\Migration;

/**
 * Class m210616_041307_add_decta_to_banks_table
 */
class m210616_041307_add_decta_to_banks_table extends Migration
{
    /**
     * @var int $bankId
     */
    private $bankId = 12;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->db->createCommand("INSERT INTO `banks` SET `ID`=$this->bankId,`Name`='Decta'")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db->createCommand("DELETE FROM `banks` WHERE `ID`=$this->bankId")->execute();

        return true;
    }
}
