<?php

use yii\db\Migration;

/**
 * Class m200228_061625_partnernewregfilds
 */
class m200228_061625_partnernewregfilds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'UrState', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('urid.status - 0 - ul 1 - ip 2 - fl')
            ->after('Name')
        );

        $this->createTable('partner_reg', [
            'ID' => $this->primaryKey(10)->unsigned(),
            'UrState' => $this->tinyInteger(1)->unsigned()->notNull()->comment('urid.status - 0 - ul 1 - ip 2 - fl'),
            'Email' => $this->string(50)->comment('email dlia activacii'),
            'EmailCode' => $this->string(50)->comment('kod dlia activacii email'),
            'DateReg' => $this->integer(10)->unsigned()->notNull()->comment('data registracii'),
            'State' => $this->tinyInteger(1)->unsigned()->notNull()->comment('status - 0 - novyii 1 - zaregistrirovan'),
            'IdPay' => $this->integer(10)->unsigned()->notNull()->comment('id pay_schet - proverochnaya registracia karty fl'),
        ]);
        $this->createIndex('Email_idx', 'partner_reg', ['Email', 'State']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'UrState');
        $this->dropTable('partner_reg');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200228_061625_partnernewregfilds cannot be reverted.\n";

        return false;
    }
    */
}
