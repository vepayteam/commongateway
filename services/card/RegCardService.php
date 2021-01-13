<?php


namespace app\services\card;


use Yii;
use app\models\api\Reguser;
use yii\base\Exception;
use app\models\bank\TCBank;
use app\models\mfo\MfoReq;
use app\services\payment\models\repositories\PaySchetRepository;
use yii\mutex\FileMutex;
use app\models\kfapi\KfCard;

/**
 * Class RegCardService
 * @package app\services\card
 */
class RegCardService
{
    /**
     * @var array
     */
    public $result = [];

    /**
     * @param MfoReq $mfo
     * @param KfCard $kfCard
     * @return array
     * @throws Exception
     */
    public function reg(MfoReq $mfo, KfCard $kfCard): array
    {
        $this->fileMutex($mfo, $kfCard);
        $this->getUser($mfo);
        $this->cardRegistration($mfo, $kfCard);
        return $this->result;
    }

    /**
     * @param MfoReq $mfo
     * @param KfCard $kfCard
     * @throws Exception
     */
    public function fileMutex(MfoReq $mfo, KfCard $kfCard): void
    {
        $this->result['mutex'] = new FileMutex();
        if (!empty($kfCard->extid)) {
            //проверка на повторный запрос
            if (!$this->result['mutex']->acquire('getPaySchetExt' . $kfCard->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paySchetRepository = new PaySchetRepository();
            $paySchet = $paySchetRepository->getPaySchetExt($kfCard->extid, 1, $mfo->mfo);
            $type = $mfo->GetReq('type');
            if ($type == 0 && $paySchet) {
                $this->result = ['status' => 1, 'message' => '', 'pay_schet_id' => $paySchet->ID, 'url' => $kfCard->GetRegForm($paySchet->ID)];
            } elseif($type == 1 && $paySchet) {
                $this->result = ['status' => 1, 'message' => '', 'pay_schet_id' => $paySchet->ID, 'url' => $mfo->getLinkOutCard($paySchet->ID)];
            }
        }
    }

    /**
     * @param MfoReq $mfo
     * @throws \Exception
     */
    private function getUser(MfoReq $mfo): void
    {
        $reguser = new Reguser();
        $this->result['user'] = $reguser->findUser('0', $mfo->mfo . '-' . time() . random_int(100, 999), md5($mfo->mfo . '-' . time()), $mfo->mfo, false);
    }

    /**
     * @param MfoReq $mfo
     * @param KfCard $kfCard
     */
    private function cardRegistration(MfoReq $mfo, KfCard $kfCard): void
    {
        $type = $mfo->GetReq('type');
        Yii::warning('/card/reg mfo=' . $mfo->mfo . " type=" . $type, 'mfo');
        if ($type == 0 && !isset($this->result['status'])) {
            //карта для автоплатежа
            $this->forAutopayment($mfo, $kfCard);
        } elseif ($type == 1 && !isset($this->result['status'])) {
            //карта для выплат
            $this->forPayments($mfo, $kfCard);
        }
    }

    /**
     * @param MfoReq $mfo
     * @param KfCard $kfCard
     */
    public function forAutopayment(MfoReq $mfo, KfCard $kfCard): void
    {
        $paySchetRepository = new PaySchetRepository();
        $data = $paySchetRepository->payActivateCard(0, $kfCard, 3, TCBank::$bank, $mfo->mfo, $this->result['user']);
        if (!empty($kfCard->extid)) {
            $this->result['mutex']->release('getPaySchetExt' . $kfCard->extid);
        }
        if (isset($data['IdPay'])) {
            $this->result = ['status' => 1, 'message' => '', 'pay_schet_id' => $data['IdPay'], 'url' => $kfCard->GetRegForm($data['IdPay'])];
        }
    }

    /**
     * @param MfoReq $mfo
     * @param KfCard $kfCard
     */
    public function forPayments(MfoReq $mfo, KfCard $kfCard): void
    {
        $paySchetRepository = new PaySchetRepository();
        $data = $paySchetRepository->payActivateCard(0, $kfCard, 3, 0, $mfo->mfo, $this->result['user']);
        if (!empty($kfCard->extid)) {
            $this->result['mutex']->release('getPaySchetExt' . $kfCard->extid);
        }
        if (isset($data['IdPay'])) {
            $this->result = ['status' => 1, 'message' => '', 'pay_schet_id' => $data['IdPay'], 'url' => $mfo->getLinkOutCard($data['IdPay'])];
        }
    }

}