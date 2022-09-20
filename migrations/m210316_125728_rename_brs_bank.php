<?php

use app\services\payment\banks\BRSAdapter;
use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m210316_125728_rename_brs_bank
 */
class m210316_125728_rename_brs_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $brs = Bank::findOne(['ID' => 7]);
        $brs->Name = 'BRS';
        $brs->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $brs = Bank::findOne(['ID' => 7]);
        $brs->Name = 'RSB';
        $brs->save(false);

        return true;
    }
}
