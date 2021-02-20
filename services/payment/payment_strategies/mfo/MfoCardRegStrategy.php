<?php


namespace app\services\payment\payment_strategies\mfo;


use app\models\api\Reguser;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\CardRegForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use yii\mutex\FileMutex;

class MfoCardRegStrategy
{
    private $cardRegByPayForm;

    public function __construct(CardRegForm $cardRegForm)
    {
        $this->cardRegByPayForm = $cardRegForm;
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws \app\services\payment\exceptions\GateException
     * @throws \Exception
     */
    public function exec()
    {
        $duplicatePaySchet = $this->getDuplicateRequest();
        if(!empty($duplicatePaySchet)) {
            return $duplicatePaySchet;
        }

        $user = $this->createUser();
        $uslugatovar = Uslugatovar::findOne(['ID' => Uslugatovar::REG_CARD_ID]);
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->cardRegByPayForm->partner, $uslugatovar);

        return $this->createPaySchet($user, $bankAdapterBuilder);
    }

    /**
     * @return PaySchet|null
     */
    private function getDuplicateRequest()
    {
        if(!empty($this->cardRegByPayForm->extid)) {
            $duplicatePaySchet = PaySchet::findOne([
                'Extid' => $this->cardRegByPayForm->extid,
                'IdUsluga' => Uslugatovar::TYPE_REG_CARD,
                'IdOrg' => $this->cardRegByPayForm->partner->ID,
            ]);
            return $duplicatePaySchet;
        } else {
            return null;
        }
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function getUslugatovar()
    {
        return Uslugatovar::findOne(['ID' => Uslugatovar::REG_CARD_ID]);
    }

    /**
     * @return \app\models\payonline\User|bool|false
     * @throws \Exception
     */
    private function createUser()
    {
        $extUser = $this->cardRegByPayForm->partner->ID
            .'-'
            .time()
            .random_int(100,999);
        $reguser = new Reguser();
        $user = $reguser->findUser(
            '0',
            $extUser,
            md5($this->cardRegByPayForm->partner->ID.'-'.time()),
            $this->cardRegByPayForm->partner->ID,
            false
        );
        return $user;
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @return PaySchet
     * @throws CreatePayException
     */
    public function createPaySchet(User $user, BankAdapterBuilder $bankAdapterBuilder)
    {
        $summPay = random_int(100, 1000);

        $paySchet = new PaySchet();

        $paySchet->IdUser = $user->ID;
        $paySchet->Bank = $this->cardRegByPayForm->type == CardRegForm::CARD_REG_TYPE_BY_OUT ? 0 : $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->cardRegByPayForm->partner->ID;
        $paySchet->Extid = $this->cardRegByPayForm->extid;
        $paySchet->QrParams = '';
        $paySchet->SummPay = $summPay;
        $paySchet->ComissSumm = $bankAdapterBuilder->getUslugatovar()->calcComiss($summPay);
        $paySchet->MerchVozn = $bankAdapterBuilder->getUslugatovar()->calcComissOrg($summPay);
        $paySchet->BankComis = $bankAdapterBuilder->getUslugatovar()->calcBankComis($summPay);
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->IsAutoPay = 0;
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;

        $paySchet->SuccessUrl = $this->cardRegByPayForm->successurl;
        $paySchet->FailedUrl = $this->cardRegByPayForm->failurl;
        $paySchet->CancelUrl = $this->cardRegByPayForm->cancelurl;
        $paySchet->PostbackUrl = $this->cardRegByPayForm->postbackurl;
        $paySchet->PostbackUrl_v2 = $this->cardRegByPayForm->postbackurl_v2;

        $paySchet->sms_accept = 1;

        if(!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }

}
