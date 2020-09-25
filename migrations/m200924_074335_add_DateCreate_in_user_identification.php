<?php

use yii\db\Migration;

/**
 * Class m200924_074335_add_DateCreate_in_user_identification
 */
class m200924_074335_add_DateCreate_in_user_identification extends Migration
{
    // Устанавливаем дефолтную дату на 24-09-2020
    const DEFAULT_VALUE = 1600905600;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'user_identification',
            'DateCreate',
            $this->integer()->defaultValue(self::DEFAULT_VALUE)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user_identification', 'DateCreate');

        return true;
    }

}
