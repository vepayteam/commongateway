<?php


namespace app\services\payment\payment_strategies\mfo;


use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;

class MfoOutCardStrategy
{
    /** @var OutCardPayForm */
    private $outCardPayForm;

    /**
     * @param OutCardPayForm $outCardPayForm
     */
    public function __construct(OutCardPayForm $outCardPayForm)
    {
        $this->outCardPayForm = $outCardPayForm;
    }


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

        $card = $this->outCardPayForm->getCardOut();
        if($card) {
            $CardToken = new CardToken();
            $this->outCardPayForm->cardnum = $CardToken->GetCardByToken($card->IdPan);
        } else {
            $cartToken = new CardToken();
            if (($token = $cartToken->CheckExistToken($this->outCardPayForm->cardnum, 0)) == 0) {
                $token = $cartToken->CreateToken($this->outCardPayForm->cardnum, 0, '');
            }
            if ($token === 0) {
                throw new CardTokenException('Ошибка при формирование токена');
            }
        }

        if(!$this->outCardPayForm->cardnum) {
            throw new CardTokenException('Ошибка при получение номера карты');
        }

        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->outCardPayForm->partner, $uslugatovar);

        $paySchet = $this->createPaySchet();
        $outCardPayResponse = $bankAdapterBuilder->getBankAdapter()->outCardPay($this->outCardPayForm);
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
                'Enable' => 1,
            ])
            ->orderBy('Priority DESC')
            ->one();
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @param Cards $card
     * @return PaySchet
     * @throws CreatePayException
     */
    private function createPaySchet(BankAdapterBuilder $bankAdapterBuilder, Cards $card)
    {
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken($card->CardNumber,$card->getMonth() . $card->getYear());
        if ($token == 0) {
            $token = $cartToken->CreateToken(
                $card->CardNumber,
                $card->getMonth() . $card->getYear(), $card->CardHolder
            );
        }

        $paySchet = new PaySchet();

        $paySchet->Status = PaySchet::STATUS_WAITING;
        $paySchet->IdKard = $card->ID;
        $paySchet->CardNum = Cards::MaskCard($card->CardNumber);
        $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($card->CardNumber));
        $paySchet->CardHolder = mb_substr($card->CardHolder, 0, 99);
        $paySchet->CardExp = $card->getMonth() . $card->getYear();
        $paySchet->IdShablon = $token;

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
