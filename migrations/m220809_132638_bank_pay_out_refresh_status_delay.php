<?php

use yii\db\Migration;

/**
 * Class m220809_132638_bank_pay_out_refresh_status_delay
 */
class m220809_132638_bank_pay_out_refresh_status_delay extends Migration
{
    public function up()
    {
        $this->addColumn(
            'banks',
            'OutCardRefreshStatusDelay',
            $this->smallInteger()->unsigned()->notNull()->defaultValue(0)
        );
    }

    public function down()
    {
        $this->dropColumn('banks', 'OutCardRefreshStatusDelay');
    }
}
