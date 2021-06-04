<?php


namespace app\services\ident;


use app\models\payonline\Uslugatovar;
use app\services\ident\jobs\RequestGetStatusJob;
use app\services\ident\jobs\RequestInitJob;
use app\services\ident\models\Ident;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\web\BadRequestHttpException;

class RequestInitStrategy
{
    /** @var Ident */
    protected $ident;

    /**
     * InitStrategy constructor.
     * @param Ident $ident
     */
    public function __construct(Ident $ident)
    {
        $this->ident = $ident;
    }

    /**
     * @return Ident
     * @throws BadRequestHttpException
     * @throws GateException
     */
    public function exec()
    {
        Yii::warning('RequestInitStrategy start identId=' . $this->ident->Id, 'ident');
        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->ident->partner, $uslugatovar);

        $this->ident->setBankScenario($bankAdapterBuilder->getBankAdapter()->getBankId());
        if(!$this->ident->validate()) {
            Yii::error('RequestInitStrategy validateError identId=' . $this->ident->Id, 'ident');
            throw new BadRequestHttpException($this->ident->GetError());
        }
        if(!$this->saveIdent($bankAdapterBuilder)) {
            Yii::error('RequestInitStrategy saveError identId=' . $this->ident->Id, 'ident');
            throw new \Exception('Ошибка при сохранении');
        }

        Yii::$app->queue->delay(10)->push(new RequestInitJob([
            'identId' => $this->ident->Id,
        ]));

        return $this->ident;
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

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @return bool
     */
    protected function saveIdent(BankAdapterBuilder $bankAdapterBuilder)
    {
        $this->ident->BankId = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $this->ident->DateCreated = time();
        $this->ident->DateUpdated = time();
        $this->ident->Status = Ident::STATUS_CREATED;
        $this->ident->Response = '[]';
        return $this->ident->save(false);
    }

}
