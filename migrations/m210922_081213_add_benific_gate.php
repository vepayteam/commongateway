<?php

use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m210922_081213_add_benific_gate
 */
class m210922_081213_add_benific_gate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $uslugatovarType = new UslugatovarType();
        $uslugatovarType->Id = UslugatovarType::REGISTRATION_BENIFIC;
        $uslugatovarType->Name = 'Регистрация бенифициата';
        $uslugatovarType->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        UslugatovarType::deleteAll(['Id' => UslugatovarType::REGISTRATION_BENIFIC]);
        return true;
    }
}
