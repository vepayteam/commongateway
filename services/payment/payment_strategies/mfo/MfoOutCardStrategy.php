<?php

namespace app\services\payment\payment_strategies\mfo;

use app\helpers\TokenHelper;
use app\models\api\Reguser;
use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\CardRegisterService;
use app\services\cards\models\PanToken;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\NotUniquePayException;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\PaymentService;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\mutex\FileMutex;
use yii\queue\redis\Queue;

class MfoOutCardStrategy
{
    /** @var OutCardPayForm */
    private $outCardPayForm;
    /** @var Queue */
    private $queue;
    /** @var CardRegisterService */
    private $cardRegisterService;
    /** @var PaymentService */
    protected $paymentService;

    /**
     * @param OutCardPayForm $outCardPayForm
     */
    public function __construct(OutCardPayForm $outCardPayForm)
    {
        $this->outCardPayForm = $outCardPayForm;
        $this->queue = \Yii::$app->queue;
        $this->cardRegisterService = Yii::$app->get(CardRegisterService::class);
    }

    /**
     * @return PaySchet
     * @throws CardTokenException
     * @throws CreatePayException
     * @throws Exception
     * @throws GateException
     * @throws \Vepay\Gateway\Client\Validator\ValidationException
     * @throws NotUniquePayException
     */
    public function exec()
    {
        $mutex = new FileMutex();
        if (!$mutex->acquire($this->outCardPayForm->getMutexKey(), 30)) {
            throw new Exception('MfoOutCardStrategy: error lock!');
        }

        $replyPay = $this->getReplyPay();
        if($replyPay && $this->outCardPayForm->extid) {
            $mutex->release($this->outCardPayForm->getMutexKey());
            throw new NotUniquePayException($replyPay->ID, $replyPay->Extid);
        }

        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }
        $validateErrors = $this->getPaymentService()->validatePaySchetWithUslugatovar($this->outCardPayForm, $uslugatovar);
        if(count($validateErrors) > 0) {
            throw new GateException($validateErrors[0]);
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->outCardPayForm->partner, $uslugatovar, $this->outCardPayForm->getCurrency());

        if(!$this->outCardPayForm->cardnum) {
            throw new CardTokenException('Ошибка при получение номера карты');
        }

        $card = $this->outCardPayForm->getCardOut();
        if ($card) {
            $cardToken = new CardToken();
            $this->outCardPayForm->cardnum = $cardToken->GetCardByToken($card->IdPan);
        } else {
            $token = TokenHelper::getOrCreateToken($this->outCardPayForm->cardnum, null, null);
            if ($token === null) {
                throw new CardTokenException('Ошибка при формировании токена.');
            }
            $card = $this->cardRegisterService->getOrCreateCard(
                PanToken::findOne($token),
                $bankAdapterBuilder->getPartnerBankGate()
            );
        }
        $paySchet = $this->createPaySchet($bankAdapterBuilder, $card);
        $this->outCardPayForm->paySchet = $paySchet;
        $outCardPayResponse = $bankAdapterBuilder->getBankAdapter()->outCardPay($this->outCardPayForm);

        if ($outCardPayResponse->status == BaseResponse::STATUS_DONE) {
            /** @todo Fix status change/check logic. */
            $paySchet->ExtBillNumber = $outCardPayResponse->trans;
            $paySchet->save(false);
        } else if ($outCardPayResponse->status == BaseResponse::STATUS_CREATED) {
            $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $paySchet->ErrorInfo = $outCardPayResponse->message;
            $paySchet->save(false);
        } else {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $outCardPayResponse->message;
            $paySchet->save(false);
            throw new CreatePayException($outCardPayResponse->message);
        }

        /** @todo Fix status change/check logic. */
        if (in_array($outCardPayResponse->status, [BaseResponse::STATUS_DONE, BaseResponse::STATUS_CREATED])) {
            $this->queue
                ->delay($bankAdapterBuilder->getBankAdapter()->getOutCardRefreshStatusDelay())
                ->push(new RefreshStatusPayJob([
                    'paySchetId' => $paySchet->ID,
                ]));
        }

        return $paySchet;
    }

    /**
     * @return array|PaySchet|null
     */
    private function getReplyPay()
    {
        try {
            Yii::info((array)$this->outCardPayForm, 'getReplyPay');
            return $this->outCardPayForm->extid ? $this->outCardPayForm->partner->getPaySchets()->where([
                    'Extid' => $this->outCardPayForm->extid,
                ])->one() : null;
        } catch (\Exception $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), $e->getFile(), $e->getLine()], 'getReplyPay');
            return null;
        }
    }

    /**
     * @return array|Uslugatovar|null
     */
    private function getUslugatovar()
    {
        return Uslugatovar::find()
            ->where([
                'IDPartner' => $this->outCardPayForm->partner->ID,
                'IsCustom'  => UslugatovarType::TOCARD,
                'IsDeleted' => 0,
            ])
            ->one();
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @param User $user
     * @param Cards $card
     * @return PaySchet
     * @throws CreatePayException
     */
    private function createPaySchet(BankAdapterBuilder $bankAdapterBuilder, Cards $card)
    {
        $paySchet = new PaySchet();

        $paySchet->IdKard = $card->ID;
        $paySchet->IdUser = $card->IdUser;
        $paySchet->CardNum = Cards::MaskCard($this->outCardPayForm->cardnum);
        $paySchet->CardHolder = mb_substr($card->CardHolder, 0, 99);
        $paySchet->CardExp = $card->getMonth() . $card->getYear();
        $paySchet->Status = PaySchet::STATUS_WAITING;
        $paySchet->IdShablon = 0;
        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->outCardPayForm->partner->ID;
        $paySchet->Extid = $this->outCardPayForm->extid;
        $paySchet->SummPay = $this->outCardPayForm->amount;
        $paySchet->CurrencyId = $this->outCardPayForm->getCurrency()->Id;

        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->IsAutoPay = 0;
        $paySchet->sms_accept = 1;

        $paySchet->FIO = $this->outCardPayForm->fullname;
        $paySchet->Dogovor = $this->outCardPayForm->document_id;

        if(!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }

}