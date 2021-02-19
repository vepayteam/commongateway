<?php


namespace app\services\payment\payment_strategies\mfo;


use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\models\PayCard;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\PaymentService;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;

class MfoOutCardStrategy
{
    /** @var OutCardPayForm */
    private $outCardPayForm;
    /** @var PaymentService */
    protected $paymentService;

    /**
     * @param OutCardPayForm $outCardPayForm
     */
    public function __construct(OutCardPayForm $outCardPayForm)
    {
        $this->outCardPayForm = $outCardPayForm;
    }

    /**
     * @return PaySchet
     * @throws CardTokenException
     * @throws CreatePayException
     * @throws Exception
     * @throws GateException
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
            throw new CreatePayException('Нарушение уникальности запроса');
        }

        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->outCardPayForm->partner, $uslugatovar);

        if(!$this->outCardPayForm->cardnum) {
            throw new CardTokenException('Ошибка при получение номера карты');
        }

        $card = $this->outCardPayForm->getCardOut();
        $token = null;
        $paySchet = null;
        if($card) {
            $cardToken = new CardToken();
            $this->outCardPayForm->cardnum = $cardToken->GetCardByToken($card->IdPan);
            $token = $card->IdPan;
            $paySchet = $this->createPaySchet($bankAdapterBuilder, $token, $card);
        } else {
            $cartToken = new CardToken();
            if (($token = $cartToken->CheckExistToken($this->outCardPayForm->cardnum, 0)) == 0) {
                $token = $cartToken->CreateToken($this->outCardPayForm->cardnum, 0, '');
            }
            if ($token === 0) {
                throw new CardTokenException('Ошибка при формирование токена');
            }
            $paySchet = $this->createPaySchet($bankAdapterBuilder, $token);
        }
        $this->outCardPayForm->paySchet = $paySchet;
        $outCardPayResponse = $bankAdapterBuilder->getBankAdapter()->outCardPay($this->outCardPayForm);

        if($outCardPayResponse->status == BaseResponse::STATUS_DONE) {
            $paySchet->ExtBillNumber = $outCardPayResponse->trans;
            $paySchet->save(false);
        } else {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $outCardPayResponse->message;
            $paySchet->save(false);
            throw new CreatePayException($outCardPayResponse->message);
        }

        return $paySchet;
    }

    /**
     * @return array|PaySchet|null
     */
    private function getReplyPay()
    {
        return $this->outCardPayForm->partner
            ->getPaySchets()
            ->where([
                'Extid' => $this->outCardPayForm->extid,
            ])
            ->one();
    }

    /**
     * @return array|Uslugatovar|null
     */
    private function getUslugatovar()
    {
        return Uslugatovar::find()
            ->where([
                'IsCustom' => UslugatovarType::TOCARD,
                'IsDeleted' => 0,
            ])
            ->one();
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @param Cards $card
     * @return PaySchet
     * @throws CreatePayException
     */
    private function createPaySchet(BankAdapterBuilder $bankAdapterBuilder, $token, Cards $card = null)
    {
        $paySchet = new PaySchet();

        if($card) {
            $paySchet->IdKard = $card->ID;
            $paySchet->CardNum = Cards::MaskCard($this->outCardPayForm->cardnum);
            $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($this->outCardPayForm->cardnum));
            $paySchet->CardHolder = mb_substr($card->CardHolder, 0, 99);
            $paySchet->CardExp = $card->getMonth() . $card->getYear();
        } else {
            $paySchet->CardNum = Cards::MaskCard($this->outCardPayForm->cardnum);
        }

        $paySchet->Status = PaySchet::STATUS_WAITING;

        $paySchet->IdShablon = 0;

        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->outCardPayForm->partner->ID;
        $paySchet->Extid = $this->outCardPayForm->extid;
        $paySchet->SummPay = $this->outCardPayForm->amount;
        $paySchet->ComissSumm = $bankAdapterBuilder->getUslugatovar()->calcComiss($paySchet->SummPay);
        $paySchet->MerchVozn = $bankAdapterBuilder->getUslugatovar()->calcComissOrg($paySchet->SummPay);
        $paySchet->BankComis = $bankAdapterBuilder->getUslugatovar()->calcBankComis($paySchet->SummPay);
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

}
