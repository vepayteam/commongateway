<?php


namespace app\services\payment\payment_strategies;


use app\helpers\TokenHelper;
use app\models\payonline\Cards;
use app\services\CardRegisterService;
use app\services\cards\models\PanToken;
use app\services\payment\banks\bank_adapter_responses\SendP2pResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

class SendP2pStrategy
{
    /** @var CardRegisterService */
    private $cardRegisterService;

    /** @var SendP2pForm */
    protected $sendP2pForm;
    /** @var SendP2pResponse */
    public $sendP2pResponse;

    /**
     * SendP2pStrategy constructor.
     * @param SendP2pForm $sendP2pForm
     */
    public function __construct(SendP2pForm $sendP2pForm)
    {
        $this->sendP2pForm = $sendP2pForm;
        $this->cardRegisterService = \Yii::$app->get(CardRegisterService::class);
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws \app\services\payment\exceptions\GateException
     */
    public function exec()
    {
        $paySchet = $this->sendP2pForm->paySchet;
        if ($paySchet->isOld()) {
            throw new CreatePayException('Время для оплаты истекло');
        }
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);
        $this->setCardPay($paySchet, $bankAdapterBuilder->getPartnerBankGate());
        $paySchet->SummPay = $this->sendP2pForm->amount * 100;
        $paySchet->OutCardPan = substr($this->sendP2pForm->outCardPan, 0, 6)
            . '***'
            . substr($this->sendP2pForm->outCardPan, -4);
        $paySchet->save(false);

        $this->sendP2pResponse = $bankAdapterBuilder->getBankAdapter()->sendP2p($this->sendP2pForm);
        $paySchet->ExtBillNumber = $this->sendP2pResponse->transac;
        $paySchet->save(false);
        return $paySchet;
    }

    /**
     * @throws CreatePayException
     */
    protected function setCardPay(PaySchet $paySchet, PartnerBankGate $partnerBankGate)
    {
        $token = TokenHelper::getOrCreateToken(
            $this->sendP2pForm->cardPan,
            sprintf('%02d', $this->sendP2pForm->cardExpMonth) . substr($this->sendP2pForm->cardExpYear, 2),
            $this->sendP2pForm->cardHolder
        );
        if ($token === null) {
            throw new CardTokenException('Ошибка при формировании токена.');
        }

        $card = $this->cardRegisterService->getOrCreateCard(
            PanToken::findOne($token),
            $partnerBankGate
        );

        $paySchet->IdKard = $card->ID;
        $paySchet->IdUser = $card->IdUser;
        $paySchet->CardNum = Cards::MaskCard($this->sendP2pForm->cardPan);
        $paySchet->CardHolder = mb_substr($this->sendP2pForm->cardHolder, 0, 99);
        $paySchet->CardExp = $this->sendP2pForm->cardExpMonth . $this->sendP2pForm->cardExpYear;
        $paySchet->IdShablon = $token;

        if (!$paySchet->save()) {
            throw new CreatePayException('Ошибка валидации данных счета');
        }
    }
}
