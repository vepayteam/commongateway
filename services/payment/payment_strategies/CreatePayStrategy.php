<?php


namespace app\services\payment\payment_strategies;


use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use yii\db\Exception;

class CreatePayStrategy
{
    /** @var CreatePayForm */
    protected $payForm;

    public function __construct(CreatePayForm $payForm)
    {
        $this->payForm = $payForm;
    }

    public function exec()
    {
        $paySchet = $this->payForm->getPaySchet();

        if($paySchet->isOld()) {
            throw new CreatePayException('Время для оплаты истекло');
        }
        $this->setCardPay($paySchet);

        // для погашений, карты маэстро только по еком надо
        if (
            $paySchet->uslugatovar->IsCustom == UslugatovarType::POGASHATF
            && Cards::GetTypeCard($this->payForm->CardNumber) == Cards::BRANDS['MAESTRO']
        ) {
            $uslugatovarType = UslugatovarType::findOne([
                'Id' => UslugatovarType::POGASHECOM,
            ]);

            /** @var PartnerBankGate $partnerBankGate */
            $partnerBankGate = PartnerBankGate::find()->where([
                'PartnerId' => $paySchet->partner->ID,
                'TU' => UslugatovarType::POGASHECOM,
            ])->orderBy('Priority DESC')->one();

            if(!$partnerBankGate) {
                throw new GateException('Нет шлюза');
            }

            $paySchet->changeGate($partnerBankGate);
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar);

        $response = $bankAdapterBuilder->getBankAdapter()->pay($this->payForm);

        if($response['status'] == 2) {

        }


        return $paySchet;
    }


    protected function setCardPay(PaySchet $paySchet)
    {
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken(
            $this->payForm->CardNumber,
            $this->payForm->CardMonth.$this->payForm->CardYear
        );

        if ($token == 0) {
            $token = $cartToken->CreateToken(
                $this->payForm->CardNumber,
                $this->payForm->CardMonth . $this->payForm->CardYear,
                $this->payForm->CardHolder
            );
        }

        $paySchet->CardNum = Cards::MaskCard($this->payForm->CardNumber);
        $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($this->payForm->CardNumber));
        $paySchet->CardHolder = mb_substr($this->payForm->CardHolder, 0, 99);
        $paySchet->CardExp = $this->payForm->CardMonth . $this->payForm->CardYear;
        $paySchet->IdShablon = $token;

        if(!$paySchet->validate() || !$paySchet->save()) {
            throw new CreatePayException('Ошибка валидации данных счета');
        }
    }

    protected function responseIsBad()
    {
        $payschets->confirmPay([
            'idpay' => $params['ID'],
            'result_code' => 2,
            'trx_id' => 0,
            'ApprovalCode' => '',
            'RRN' => '',
            'message' => $ret['message']
        ]);
    }
}
