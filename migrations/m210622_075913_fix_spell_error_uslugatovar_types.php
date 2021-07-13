<?php

use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m210622_075913_fix_spell_error_uslugatovar_types
 */
class m210622_075913_fix_spell_error_uslugatovar_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        UslugatovarType::updateAll([
            'Name' => 'Упрощенная идентификация пользователей'
        ], ['Name' => 'Упращенная идентификация пользователей']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        UslugatovarType::updateAll([
            'Name' => 'Упращенная идентификация пользователей'
        ], ['Name' => 'Упрощенная идентификация пользователей']);
    }
}
