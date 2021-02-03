<?php


namespace app\services\payment\jobs;


use app\models\crypt\CardToken;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\BaseObject;
use yii\mutex\FileMutex;
use yii\queue\Queue;

class RecurrentPayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $partnerId;
    public $uslugatovarId;
    public $paySchetId;
    public $autoPayFormSerialized;

    /** @var Partner */
    private $partner;
    /** @var Uslugatovar */
    private $uslugatovar;
    /** @var PaySchet */
    private $paySchet;
    /** @var AutoPayForm */
    private $autoPayForm;
    /** @var BankAdapterBuilder */
    private $bankAdapterBuilder;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $this->load();

        $mutexKey = $this->autoPayForm->getMutexKey();
        $mutex = new FileMutex();
        if (!$mutex->acquire($mutexKey, 30)) {
            Yii::error('getPaySchetExt: error lock!', 'mfo');
            throw new CreatePayException('getPaySchetExt: error lock!');
        }

        try {
            Yii::warning('RecurrentPayJob autoPay=' . $this->paySchet->ID . $this->autoPayForm->extid, 'mfo');
            $createRecurrentPayResponse = $this->bankAdapterBuilder->getBankAdapter()->recurrentPay($this->autoPayForm);
        } catch (GateException $e) {
            $this->paySchet->Status = PaySchet::STATUS_ERROR;
            $this->paySchet->ErrorInfo = $e->getMessage();
        } catch (\Exception $e) {
            $mutex->release($mutexKey);
            throw $e;
        }

        $this->paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
        $this->paySchet->ErrorInfo = 'Ожидается обновление статуса';
        if($createRecurrentPayResponse->status == BaseResponse::STATUS_DONE) {
            Yii::warning('RecurrentPayJob Set ExtBillNumber autoPay=' . $this->paySchet->ID . $this->autoPayForm->extid, 'mfo');
            $this->paySchet->ExtBillNumber = $createRecurrentPayResponse->transac;
        } else {
            Yii::warning('RecurrentPayJob errorResponse autoPay=' . $this->paySchet->ID . $this->autoPayForm->extid, 'mfo');

        }

        Yii::$app->queue->push(new RefreshStatusPayJob([
            'paySchetId' => $this->paySchet->ID,
        ]));

        $this->paySchet->save(false);
        $mutex->release($mutexKey);
    }

    /**
     * @throws GateException
     */
    public function load()
    {
        $this->partner = Partner::findOne(['ID' => $this->partnerId]);
        $this->uslugatovar = Uslugatovar::findOne(['ID' => $this->uslugatovarId]);
        $this->paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);

        $this->autoPayForm = new AutoPayForm();
        $this->autoPayForm->unserialize($this->autoPayFormSerialized);

        $this->bankAdapterBuilder = new BankAdapterBuilder();
        $this->bankAdapterBuilder->build($this->partner, $this->uslugatovar);

        $card = $this->autoPayForm->getCard();
        $cardnum = null;
        if ($this->autoPayForm->getCard()->IdPan > 0) {
            Yii::warning('New CardToken: error lock!', 'mfo');
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($card->IdPan);
        }

        if(!$cardnum) {
            throw new CreatePayException('empty card');
        }
        $this->autoPayForm->getCard()->CardNumber = $cardnum;
    }
}
