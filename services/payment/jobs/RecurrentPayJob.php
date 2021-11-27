<?php


namespace app\services\payment\jobs;


use app\models\crypt\CardToken;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\queue\JobPriorityInterface;
use app\services\payment\banks\ADGroupBankAdapter;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\BRSAdapter;
use app\services\payment\banks\DectaAdapter;
use app\services\payment\banks\FortaTechAdapter;
use app\services\payment\banks\MTSBankAdapter;
use app\services\payment\banks\TKBankAdapter;
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
    private const RECURRENT_STATUS_PAY_JOB_DELAY = 30;

    public $paySchetId;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        $paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);
        $autoPayForm = $this->buildForm($paySchet);
        $bankAdapter = $this->buildAdapter($paySchet);

        try {
            Yii::warning('RecurrentPayJob autoPay=' . $paySchet->ID . ' ' . $autoPayForm->extid, 'mfo');
            $createRecurrentPayResponse = $bankAdapter->recurrentPay($autoPayForm);
        } catch (GateException $e) {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $e->getMessage();
            $paySchet->save(false);
            return $paySchet;
        } catch (\Exception $e) {
            throw $e;
        }

        $paySchet->RRN = $createRecurrentPayResponse->rrn;
        $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
        $paySchet->ErrorInfo = 'Ожидается обновление статуса';
        $paySchet->ExtBillNumber = $createRecurrentPayResponse->transac;
        if($createRecurrentPayResponse->status == BaseResponse::STATUS_DONE) {
            Yii::warning('RecurrentPayJob Set ExtBillNumber autoPay=' . $paySchet->ID . $autoPayForm->extid, 'mfo');
        } else {
            Yii::warning('RecurrentPayJob errorResponse autoPay=' . $paySchet->ID . $autoPayForm->extid.'. Message: '.$createRecurrentPayResponse->message, 'mfo');
        }

        Yii::$app->queue
            ->delay(self::RECURRENT_STATUS_PAY_JOB_DELAY)
            ->push(new RefreshStatusPayJob([
                'paySchetId' => $paySchet->ID,
            ]));

        $paySchet->save(false);
        return $paySchet;
    }

    /**
     * @param PaySchet $paySchet
     * @return AutoPayForm
     * @throws CreatePayException
     */
    public function buildForm(PaySchet $paySchet)
    {
        $autoPayForm = new AutoPayForm();
        $autoPayForm->amount = $paySchet->SummPay;
        $autoPayForm->document_id = $paySchet->Dogovor;
        $autoPayForm->fullname = $paySchet->FIO;
        $autoPayForm->extid = $paySchet->Extid;
        $autoPayForm->descript = '';
        $autoPayForm->card = $paySchet->IdKard;
        $autoPayForm->postbackurl = $paySchet->PostbackUrl;
        $autoPayForm->postbackurl_v2 = $paySchet->PostbackUrl_v2;

        $autoPayForm->paySchet = $paySchet;
        $autoPayForm->partner = $paySchet->partner;

        $card = $autoPayForm->getCard();
        $cardnum = null;
        if ($autoPayForm->getCard()->IdPan > 0) {
            Yii::warning('New CardToken: error lock!', 'mfo');
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($card->IdPan);
        }

        if(!$cardnum) {
            throw new CreatePayException('empty card');
        }
        $autoPayForm->getCard()->CardNumber = $cardnum;
        return $autoPayForm;
    }

    /**
     * @param PaySchet $paySchet
     * @return \app\services\payment\banks\IBankAdapter
     * @throws GateException
     */
    public function buildAdapter(PaySchet $paySchet)
    {
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);
        return $bankAdapterBuilder->getBankAdapter();
    }
}
