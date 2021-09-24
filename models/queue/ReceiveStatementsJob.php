<?php


namespace app\models\queue;

use app\models\mfo\statements\ReceiveStatemets;
use app\models\payonline\Partner;
use Yii;

use yii\base\BaseObject;
use yii\db\Exception;

class ReceiveStatementsJob extends BaseObject implements \yii\queue\JobInterface
{
    public $IdPartner;
    public $TypeAcc;
    public $datefrom;
    public $dateto;

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        if ($partner) {
            $ReceiveStatemets = new ReceiveStatemets($partner);
            try {
                Yii::warning('Run ReceiveStatementsJob mfo=' . $this->IdPartner . ' date='.$this->datefrom."-".$this->dateto , 'rsbcron');
                $ReceiveStatemets->UpdateStatemets($this->TypeAcc, $this->datefrom, $this->dateto);
            } catch (Exception $e) {
                Yii::warning('Error ReceiveStatementsJob: ' . $e->getMessage(), 'rsbcron');
            }
        }
    }
}
