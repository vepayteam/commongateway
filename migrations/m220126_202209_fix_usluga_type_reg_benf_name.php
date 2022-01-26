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
        $q = 'UPDATE `vepay`.`uslugatovar_types` SET `Name` = \'Регистрация бенефицара\' WHERE `Id` = ' . UslugatovarType::REGISTRATION_BENIFIC;
        Yii::$app->db->createCommand($q)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $q = 'UPDATE `vepay`.`uslugatovar_types` SET `Name` = \'Регистрация бенифициата\' WHERE `Id` = ' . UslugatovarType::REGISTRATION_BENIFIC;
        Yii::$app->db->createCommand($q)->execute();
    }
}
