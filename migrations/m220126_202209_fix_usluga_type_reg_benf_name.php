<?php

use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m220126_202209_fix_usluga_type_reg_benf_name
 */
class m220126_202209_fix_usluga_type_reg_benf_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $uslugatovarType = UslugatovarType::findOne(['Id' => UslugatovarType::REGISTRATION_BENIFIC]);
        $uslugatovarType->Name = 'Регистрация бенефицара';
        $uslugatovarType->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $uslugatovarType = UslugatovarType::findOne(['Id' => UslugatovarType::REGISTRATION_BENIFIC]);
        $uslugatovarType->Name = 'Регистрация бенифициата';
        $uslugatovarType->save(false);
    }
}
