<?php

use app\services\payment\models\PaySchet;
use yii\db\Migration;

/**
 * Class m201105_065234_add_3ds_fields_in_pay_schet
 */
class m201105_065234_add_3ds_fields_in_pay_schet extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            PaySchet::tableName(),
            'Version3DS',
            $this->string()->after('PayType')
        );

        $this->addColumn(
            PaySchet::tableName(),
            'IsNeed3DSVerif',
            $this->boolean()->after('Version3DS')
        );

        $this->addColumn(
            PaySchet::tableName(),
            'DsTransId',
            $this->string()->after('IsNeed3DSVerif')
        );

        $this->addColumn(
            PaySchet::tableName(),
            'Eci',
            $this->string()->after('DsTransId')
        );

        $this->addColumn(
            PaySchet::tableName(),
            'AuthValue3DS',
            $this->string()->after('Eci')
        );

        $this->addColumn(
            PaySchet::tableName(),
            'CardRefId3DS',
            $this->string()->after('AuthValue3DS')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(PaySchet::tableName(), 'Version3DS');
        $this->dropColumn(PaySchet::tableName(), 'IsNeed3DSVerif');
        $this->dropColumn(PaySchet::tableName(), 'DsTransId');
        $this->dropColumn(PaySchet::tableName(), 'Eci');
        $this->dropColumn(PaySchet::tableName(), 'AuthValue3DS');
        $this->dropColumn(PaySchet::tableName(), 'CardRefId3DS');

        return true;
    }

}
