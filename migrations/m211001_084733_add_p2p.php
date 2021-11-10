<?php

use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m211001_084733_add_p2p
 */
class m211001_084733_add_p2p extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $uslugatovarType = new UslugatovarType();
        $uslugatovarType->Id = UslugatovarType::P2P;
        $uslugatovarType->Name = 'Перевод с карты на карту';
        $uslugatovarType->save(false);

        $this->addColumn(
            PaySchet::tableName(),
            'OutCardPan',
            $this->string(20)->after('CardNum')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        UslugatovarType::deleteAll(['Id' => UslugatovarType::P2P]);
        $this->dropColumn(
            PaySchet::tableName(),
            'OutCardPan'
        );
        return true;
    }
}
