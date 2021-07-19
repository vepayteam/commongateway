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
use app\services\payment\models\Bank;
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

        Bank::updateAll(['ChannelName' => 'vepay'], ['ID' => 0]);
        Bank::updateAll(['ChannelName' => 'Russia'], ['ID' => 1]);
        Bank::updateAll(['ChannelName' => 'TKB'], ['ID' => TKBankAdapter::$bank]);
        Bank::updateAll(['ChannelName' => 'MTS Bank'], ['ID' => MTSBank::$bank]);
        Bank::updateAll(['ChannelName' => 'AD Group Bank'], ['ID' => ADGBank::$bank]);
        Bank::updateAll(['ChannelName' => 'AD Group'], ['ID' => ADGroupBankAdapter::$bank]);
        Bank::updateAll(['ChannelName' => 'BRS'], ['ID' => BRSAdapter::$bank]);
        Bank::updateAll(['ChannelName' => 'Cauri'], ['ID' => CauriAdapter::$bank]);
        Bank::updateAll(['ChannelName' => 'FortaTech'], ['ID' => FortaTechAdapter::$bank]);
        Bank::updateAll(['ChannelName' => 'Walleto'], ['ID' => WalletoBankAdapter::$bank]);
        Bank::updateAll(['ChannelName' => 'Runa'], ['ID' => RunaBankAdapter::$bank]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'ChannelName');
    }
}
