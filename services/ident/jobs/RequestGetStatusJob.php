<?php


namespace app\services\ident\jobs;


use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\Queue;

class RequestGetStatusJob extends RequestInitJob implements \yii\queue\JobInterface
{
    const MAX_EXEC_TIME = 60 * 60 * 6;

    public $identId;

    /** @var Ident */
    protected $ident;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        Yii::warning('RequestGetStatusJob start identId=' . $this->identId, 'ident');
        $this->initIdent();
        $bankAdapterBuilder = $this->initBankAdapterBuilder();

        try {
            $identGetStatusResponse = $bankAdapterBuilder->getBankAdapter()->identGetStatus($this->ident);

            Yii::warning('RequestGetStatusJob resultRequest $identGetStatusResponse: ' . Json::encode($identGetStatusResponse->getAttributes()), 'ident');

            if($this->ident->Status == BaseResponse::STATUS_DONE) {
                $this->ident->Status = $identGetStatusResponse->status;
                $this->ident->Response = Json::encode($identGetStatusResponse->response);
            } elseif (
                $this->ident->Status == BaseResponse::STATUS_CREATED
                && $this->ident->DateCreated + self::MAX_EXEC_TIME > time()
            ) {
                $this->ident->Status = $identGetStatusResponse->status;
                $this->ident->Response = Json::encode($identGetStatusResponse->response);
                Yii::$app->queue->delay(5 * 60)->push(new RequestGetStatusJob([
                    'identId' => $this->identId,
                ]));
            } elseif ($this->ident->Status == BaseResponse::STATUS_CREATED) {
                $this->ident->Status = Ident::STATUS_TIMEOUT;
                $this->ident->Response = Json::encode(
                    $identGetStatusResponse->response ?? ['message' => 'Таймаут получения статуса']
                );
            }
        } catch (\Exception $e) {
            Yii::error('RequestGetStatusJob error ' . $e->getMessage(), 'ident');
            $this->ident->Status = Ident::STATUS_ERROR;
            $this->ident->Response = $e->getMessage();
        }
        $this->ident->DateUpdated = time();
        $this->ident->save(false);
    }
}
