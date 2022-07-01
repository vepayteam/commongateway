<?php

use yii\db\Migration;

/**
 * Class m220525_102427_add_yandex_pay_fields_to_partner_table
 */
class m220525_102427_add_yandex_pay_fields_to_partner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'yandexPayMerchantId', $this->string(50)->null());
        $this->addColumn('partner', 'yandexPayAuthPrivate', $this->string(50)->null());
        $this->addColumn('partner', 'yandexPayAuthPublic', $this->string(50)->null());
        $this->addColumn('partner', 'yandexPayEncryptionPrivate', $this->string(50)->null());
        $this->addColumn('partner', 'yandexPayEncryptionPublic', $this->string(50)->null());
        $this->addColumn('partner', 'isUseYandexPay', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'yandexPayMerchantId');
        $this->dropColumn('partner', 'yandexPayAuthPrivate');
        $this->dropColumn('partner', 'yandexPayAuthPublic');
        $this->dropColumn('partner', 'yandexPayEncryptionPrivate');
        $this->dropColumn('partner', 'yandexPayEncryptionPublic');
        $this->dropColumn('partner', 'isUseYandexPay');
    }
}
