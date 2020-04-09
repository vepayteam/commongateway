<?php


namespace app\models\bank;


use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * @property Payschets $payschets
 */
class WithdrawTCBankIterable extends TCBank implements \Iterator
{

    private $ordersId;
    private $index;
    private $payschets;

    public function __construct(array $ordersIds, Payschets $payschets)
    {
        $this->ordersId = $ordersIds;
        $this->payschets = $payschets;
        parent::__construct();
    }

    public static function buildWithoutPayschets(array $ordersIds): self
    {
        return new self($ordersIds, new Payschets());
    }

    public function start()
    {
        foreach ($this as $key => $value) {
            if (is_array($value)) {
                if ($value['IsCustom'] == TU::$POGASHECOM or $value['IsCustom'] == TU::$AVTOPLATECOM) {
                    $this->SetMfoGate(self::$ECOMGATE, [
                        'KeyTkbEcom'=>$value['KeyTkbEcom'],
                        'LoginTkbEcom'=>$value['LoginTkbEcom']
                    ]);
                } else {
                    $this->SetMfoGate(self::$AFTGATE, [
                        'LoginTkbAft'=>$value['LoginTkbAft'],
                        'KeyTkbAft'=>$value['KeyTkbAft']
                    ]);
                }
                Yii::warning('AutoRevers: id='.$this->ordersId[$key], 'rsbcron');
                if (Yii::$app->request->isConsoleRequest) {
                    echo 'AutoRevers: id='.$this->ordersId[$key]."\r\n";
                }
                $answer = $this->reversOrder($this->ordersId[$key]);
                if ($answer['state'] != 0) {
                    $this->payschets->SetReversPay($this->ordersId[$key]);
                }
            }
        }
    }

    public function test()
    {
        $this->ordersId = [9948];
        $this->start();
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {

        $currentId = $this->ordersId[$this->index];
        $command = new Query();
        $command
            ->select('p.LoginTkbEcom, p.KeyTkbEcom, p.LoginTkbAft, p.KeyTkbAft, order.Status, us.IsCustom')
            ->from('pay_schet as order')
            ->leftJoin('partner as p', 'p.ID = order.IdOrg')
            ->leftJoin('uslugatovar AS us', 'us.ID = order.IdUsluga')
            ->where(['order.ID' => $currentId, 'order.Status' => 1]);
        $result = $command->one();
        return $result;
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->index;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        if (count($this->ordersId) == $this->index) {
            return false;
        }
        return true;
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->index = 0;
    }
}