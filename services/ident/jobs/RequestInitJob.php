<?php


namespace app\services\ident\jobs;


use app\models\payonline\Uslugatovar;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\Queue;

class RequestInitJob extends BaseObject implements \yii\queue\JobInterface
{
    public $identId;
    /** @var Ident $ident */
    protected $ident;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $this->initIdent();
        $bankAdapterBuilder = $this->initBankAdapterBuilder();

        try {
            $identResponse = $bankAdapterBuilder->getBankAdapter()->identInit($this->ident);
            if($identResponse->status !== BaseResponse::STATUS_DONE) {
                $this->ident->Status = Ident::STATUS_ERROR;
                $this->ident->Response = Json::encode($identResponse->response);
            } else {
                $this->ident->Status = Ident::STATUS_WAITING;
                $this->ident->Response = Json::encode($identResponse->response);

                Yii::$app->queue->delay(2 * 60)->push(new RequestGetStatusJob([
                    'identId' => $this->identId,
                ]));
            }
        } catch (\Exception $e) {
            Yii::error('RequestInitJob error ' . $e->getMessage());
            $this->ident->Status = Ident::STATUS_ERROR;
            $this->ident->Response = Json::encode(['message' => $e->getMessage()]);
        }
        $this->ident->save(false);
    }

    /**
     * @return bool
     */
    protected function initIdent()
    {
        $this->ident = Ident::findOne(['Id' => $this->identId]);
        if(!empty($this->ident)) {
            return true;
        } else {
            Yii::warning('RequestJob ident not dount id=' . $this->identId, 'ident');
            return false;
        }
    }

    /**
     * @return BankAdapterBuilder
     * @throws GateException
     */
    protected function initBankAdapterBuilder()
    {
        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($this->ident->partner, $uslugatovar, $this->ident->bank);
        return $bankAdapterBuilder;
    }

    /**
     * @return Uslugatovar|null
     */
    protected function getUslugatovar()
    {
        return $this->ident->partner->getUslugatovars()->where([
            'IsCustom' => UslugatovarType::IDENT,
            'IsDeleted' => 0,
        ])->one();
    }
}
