<?php

use app\services\payment\models\UslugatovarType;
use yii\db\Migration;
use yii\db\Query;

/**
 * Class m210914_145315_add_brs_uslugatovar_type
 */
class m210914_145315_add_brs_uslugatovar_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!(new Query())->from('uslugatovar_types')->andWhere(['Id' => UslugatovarType::TRANSFER_B2C_SBP])->exists()) {
            $this->insert('uslugatovar_types', [
                'Id'   => UslugatovarType::TRANSFER_B2C_SBP,
                'Name' => 'Перевод B2C СБП',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('uslugatovar_types', ['Id' => UslugatovarType::TRANSFER_B2C_SBP]);
    }
}
