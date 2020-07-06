    <?php

use yii\db\Migration;

/**
 * Class m200605_132253_rsstate
 */
class m200605_132253_rsstate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet','RCCode', $this->string(10));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet','RCCode');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200605_132253_rsstate cannot be reverted.\n";

        return false;
    }
    */
}
