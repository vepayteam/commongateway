<?php

use app\models\payonline\Partner;
use yii\db\Migration;

/**
 * Class m201001_052755_add_mts_gates
 */
class m201001_052755_add_mts_gates extends Migration
{
    const GATES = ['Ecom','Vyvod','Auto','Perevod','OctVyvod','OctPerevod'];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $after = 'MtsTokenOct';
        foreach (self::GATES as $k => $gate) {
            $this->addColumn(
                Partner::tableName(),
                'MtsLogin' . $gate,
                $this->string()->after($k == 0 ? $after : 'MtsToken' . self::GATES[$k - 1])
            );
            $this->addColumn(
                Partner::tableName(),
                'MtsPassword' . $gate,
                $this->string()->after('MtsLogin' . $gate)
            );
            $this->addColumn(
                Partner::tableName(),
                'MtsToken' . $gate,
                $this->string()->after('MtsPassword' . $gate)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (self::GATES as $k => $gate) {
            $this->dropColumn(Partner::tableName(), 'MtsLogin' . $gate);
            $this->dropColumn(Partner::tableName(), 'MtsPassword' . $gate);
            $this->dropColumn(Partner::tableName(), 'MtsToken' . $gate);
        }

        return true;
    }

}
