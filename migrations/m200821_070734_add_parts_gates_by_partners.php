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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Partner::tableName(), 'LoginTkbParts');
        $this->dropColumn(Partner::tableName(), 'KeyTkbParts');
        $this->dropColumn(Partner::tableName(), 'SchetTcbParts');

        return true;
    }
}
