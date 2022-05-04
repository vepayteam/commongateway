<?php

use app\models\payonline\Partner;
use yii\db\Migration;

/**
 * Class m200821_070734_add_parts_gates_by_partners
 */
class m200821_070734_add_parts_gates_by_partners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Partner::tableName(), 'LoginTkbParts', $this->string()->after('KeyTkbJkh'));
        $this->addColumn(Partner::tableName(), 'KeyTkbParts', $this->string(400)->after('LoginTkbParts'));
        $this->addColumn(Partner::tableName(), 'SchetTcbParts', $this->string(400)->after('KeyTkbParts'));

        $this->addColumn(Partner::tableName(), 'MtsLoginParts', $this->string()->after('MtsTokenOct'));
        $this->addColumn(Partner::tableName(), 'MtsPasswordParts', $this->string(400)->after('MtsTokenOct'));
        $this->addColumn(Partner::tableName(), 'MtsTokenParts', $this->string(400)->after('MtsPasswordParts'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Partner::tableName(), 'LoginTkbParts');
        $this->dropColumn(Partner::tableName(), 'KeyTkbParts');
        $this->dropColumn(Partner::tableName(), 'SchetTcbParts');

        $this->dropColumn(Partner::tableName(), 'MtsLoginParts');
        $this->dropColumn(Partner::tableName(), 'MtsPasswordParts');
        $this->dropColumn(Partner::tableName(), 'MtsTokenParts');

        return true;
    }
}
