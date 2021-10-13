<?php

use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m210430_065413_add_ident_gate
 */
class m210430_065413_add_ident_gate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $uslugatovarType = new UslugatovarType();
        $uslugatovarType->Id = UslugatovarType::IDENT;
        $uslugatovarType->Name = UslugatovarType::typeList()[UslugatovarType::IDENT];
        $uslugatovarType->save(false);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        UslugatovarType::deleteAll(['Id' => UslugatovarType::IDENT]);
    }

}
