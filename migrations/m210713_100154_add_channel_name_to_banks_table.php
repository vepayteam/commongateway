<?php

use app\models\bank\ADGBank;
use app\models\bank\MTSBank;
use app\services\payment\banks\ADGroupBankAdapter;
use app\services\payment\banks\BRSAdapter;
use app\services\payment\banks\CauriAdapter;
use app\services\payment\banks\FortaTechAdapter;
use app\services\payment\banks\RunaBankAdapter;
use app\services\payment\banks\TKBankAdapter;
use app\services\payment\banks\WalletoBankAdapter;
use yii\db\Migration;

/**
 * Class m210713_100154_add_en_name_to_banks_table
 */
class m210713_100154_add_channel_name_to_banks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('banks', 'ChannelName', $this->string(250)->after('Name'));

        $this->update('banks', ['ChannelName' => 'vepay'], ['ID' => 0]);
        $this->update('banks', ['ChannelName' => 'Russia'], ['ID' => 1]);
        $this->update('banks', ['ChannelName' => 'TKB'], ['ID' => TKBankAdapter::$bank]);
        $this->update('banks', ['ChannelName' => 'MTS Bank'], ['ID' => MTSBank::$bank]);
        $this->update('banks', ['ChannelName' => 'AD Group Bank'], ['ID' => ADGBank::$bank]);
        $this->update('banks', ['ChannelName' => 'AD Group'], ['ID' => ADGroupBankAdapter::$bank]);
        $this->update('banks', ['ChannelName' => 'BRS'], ['ID' => BRSAdapter::$bank]);
        $this->update('banks', ['ChannelName' => 'Cauri'], ['ID' => CauriAdapter::$bank]);
        $this->update('banks', ['ChannelName' => 'FortaTech'], ['ID' => FortaTechAdapter::$bank]);
        $this->update('banks', ['ChannelName' => 'Walleto'], ['ID' => WalletoBankAdapter::$bank]);
        $this->update('banks', ['ChannelName' => 'Runa'], ['ID' => RunaBankAdapter::$bank]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'ChannelName');
    }
}
