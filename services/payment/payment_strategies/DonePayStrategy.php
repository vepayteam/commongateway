<?php

namespace app\services\payment\payment_strategies;

use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\TKBankRefusalException;
use app\services\payment\forms\DonePayForm;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\PaySchet;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class DonePayStrategy
{
    /** @var DonePayForm */
    protected $donePayForm;

    /** @var array|null */
    protected $donePayResponse;

    /**
     * DonePayStrategy constructor.
     * @param DonePayForm $donePayForm
     */
    public function __construct(DonePayForm $donePayForm)
    {
        $this->donePayForm = $donePayForm;
    }

    public function exec()
    {
        Yii::info('DonePayStrategy exec. IdPay=' . $this->donePayForm->IdPay);

        // для случаев, если пользователь возвращается к нам без ИД счета, но с транзакцией
        if (!empty($this->donePayForm->trans)) {
            Yii::info('DonePayStrategy exec. IdPay=' . $this->donePayForm->IdPay . ' trans=' . $this->donePayForm->trans);

            $paySchet = PaySchet::find()
                ->where(['ExtBillNumber' => $this->donePayForm->trans])
                ->orderBy('ID DESC')->one();
        } else {
            $paySchet = $this->donePayForm->getPaySchet();
        }

        Yii::info('DonePayStrategy exec. PaySchet ID=' . $paySchet->ID . ' Status=' . $paySchet->Status);

        if ($paySchet && $paySchet->Status == PaySchet::STATUS_WAITING) {
            Yii::info('DonePayStrategy exec. PaySchet ID=' . $paySchet->ID
                . ' partner=' . $paySchet->partner->ID
                . ' uslugatovar=' . $paySchet->uslugatovar->ID
                . ' bank=' . $paySchet->bank->ID
                . ' currency=' . $paySchet->currency->Id);

            try {
                $bankAdapterBuilder = new BankAdapterBuilder();
                $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);

                $this->donePayResponse = $bankAdapterBuilder->getBankAdapter()->confirm($this->donePayForm);

                Yii::info('DonePayStrategy exec. PaySchet ID=' . $paySchet->ID . ' donePayResponse=' . Json::encode($this->donePayResponse));
            } catch (TKBankRefusalException $e) {
                Yii::error(['DonePayStrategy tkbank refusal exception paySchet.ID=' . $paySchet->ID, $e]);

                $paySchet->Status = PaySchet::STATUS_ERROR;
                $paySchet->ErrorInfo = $e->getMessage();
                $paySchet->save(false);

                return $paySchet;
            } catch (\Exception $e) {
                Yii::error('DonePayStrategy exec. PaySchet ID=' . $paySchet->ID . ' exception=' . $e->getMessage());
            }

            Yii::$app->queue->delay(10)->push(new RefreshStatusPayJob([
                'paySchetId' => $paySchet->ID,
            ]));
            return $paySchet;
        } else {
            Yii::info('DonePayStrategy exec. PaySchet not found IdPay=' . $this->donePayForm->IdPay);

            throw new NotFoundHttpException();
        }
    }

    /**
     * @return array|null
     */
    public function getDonePayResponse()
    {
        return $this->donePayResponse;
    }

}