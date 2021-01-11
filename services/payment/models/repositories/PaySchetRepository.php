<?php


namespace app\services\payment\models\repositories;

use Yii;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\services\payment\models\PaySchet;
use app\models\kfapi\KfCard;
use app\models\payonline\User;

/**
 * Class PaySchetRepository
 * @package app\services\payment\models\repository
 */
class PaySchetRepository
{
    /**
     * @var PaySchet
     */
    public $model;

    public function __construct(?PaySchet $model = null)
    {
        $this->model = $model ?? new PaySchet();
    }

    /**
     * payActivateCard
     * @param int $idCardActivate
     * @param KfCard $kfCard
     * @param int $TypwWidget
     * @param int|null $bank
     * @param int|null $org
     * @param User $user
     * @return array|null
     */
    public function payActivateCard(int $idCardActivate, KfCard $kfCard, int $TypwWidget, ?int $bank = 0, ?int $org = 0, User $user): ?array
    {
        $ret = null;
        try {
            $sum = random_int(100, 1000) / 100.0;
        } catch (\Exception $e) {
            $sum = 100;
        }
        $this->setProvparams($sum);
        if ($this->Provparams->Usluga) {
            $this->payActivateCardSetProp(0, $idCardActivate, $TypwWidget, $bank, $org, $kfCard->extid, 0, $kfCard->timeout * 60, $user, $kfCard);
            $this->model->save();
            $ret = ['IdPay' => $this->model->ID, 'Sum' => $sum];
        }
        return $ret;
    }

    /**
     * PaySchet set property
     * @param int $agent
     * @param int $idCardActivate
     * @param int $TypeWidget
     * @param int $Bank
     * @param int $IdOrg
     * @param string $Extid
     * @param int $AutoPayIdGate
     * @param int $timeout
     * @param User|null $user
     * @param KfCard $kfCard
     */
    private function payActivateCardSetProp(int $agent = 0, int $idCardActivate = 0, int $TypeWidget = 0, int $Bank = 0, int $IdOrg = 0, string $Extid = '', int $AutoPayIdGate = 0, int $timeout = 86400, User $user = null, KfCard $kfCard): void
    {
        $this->model->IdUsluga = $this->Provparams->Usluga->ID;
        $this->model->IdQrProv = intval($this->Provparams->Usluga->ProfitIdProvider);
        $this->model->QrParams = implode("|", $this->Provparams->param);
        $this->model->IdUser = $user ? $user->ID : 0;
        $this->model->SummPay = $this->Provparams->summ;
        $this->model->UserClickPay = 0;
        $this->model->DateCreate = time();
        $this->model->IdKard = $idCardActivate;
        $this->model->Status = 0;
        $this->model->DateOplat = 0;
        $this->model->DateLastUpdate = time();
        $this->model->PayType = 0;
        $this->model->TimeElapsed = $timeout;
        $this->model->ExtKeyAcces = 0;
        $this->model->CountSendOK = 0;
        $this->model->Period = 0;
        $this->model->ComissSumm = $this->Provparams->calcComiss();
        $this->model->MerchVozn = $this->Provparams->calcMerchVozn();
        $this->model->BankComis = $this->Provparams->calcBankComis();
        $this->model->Schetcheks = '';
        $this->model->IdAgent = $agent;
        $this->model->IsAutoPay = $AutoPayIdGate > 0 ? 1 : 0;
        $this->model->AutoPayIdGate = $AutoPayIdGate;
        $this->model->TypeWidget = $TypeWidget;
        $this->model->Bank = $Bank;
        $this->model->IdOrg = $IdOrg;
        $this->model->Extid = $Extid;
        $this->model->sms_accept = 0;
        $this->model->Dogovor = '';
        $this->model->FIO = '';
        $this->model->SuccessUrl = $kfCard->successurl ?? '';
        $this->model->FailedUrl = $kfCard->failurl ?? '';
        $this->model->CancelUrl = $kfCard->cancelurl ?? '';
    }

    /**
     * Set provparams
     * @param float|int $sum
     */
    private function setProvparams($sum): void
    {
        $formData = [
            'Provparams' => [
                'prov' => 1,
                'param' => [0 => $sum],
                'summ' => $sum
            ]
        ];
        $this->Provparams = new Provparams();
        $this->Provparams->load($formData);
        $this->Provparams->summ = round(floatval($this->Provparams->summ) * 100.0);
        $this->Provparams->Usluga = Uslugatovar::findOne(['ID' => $this->Provparams->prov]);
    }


    /**
     * @param string $extid
     * @param string $usl
     * @param int $IdOrg
     * @return PaySchet|null
     */
    public function getPaySchetExt(string $extid, string $usl, int $IdOrg): ?PaySchet
    {
        return $this->model->find()
            ->where(['Extid' => $extid])
            ->andWhere(['IdUsluga' => $usl])
            ->andWhere(['IdOrg' => $IdOrg])
            ->andWhere('DateCreate > UNIX_TIMESTAMP() - 86400 * 100')
            ->one();
    }
}
