<?php


namespace app\services\balance;


use app\models\payonline\BalancePartner;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\balance\traits\PartsTrait;
use app\services\payment\models\PaySchet;

class BalanceService
{
    use PartsTrait;

    /**
     * @param PaySchet $paySchet
     * @return bool
     * @throws \yii\db\Exception
     */
    public function changeBalance(PaySchet $paySchet)
    {
        if(isset($paySchet->partner->SchetTcbNominal) && !empty($paySchet->partner->SchetTcbNominal)) {
            return $this->changeBalanceIfHaveNominalSchet($paySchet);
        } else {
            return $this->changeBalanceIfNotHaveNominalSchet($paySchet);
        }
    }

    /**
     * @param PaySchet $paySchet
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function changeBalanceIfHaveNominalSchet(PaySchet $paySchet)
    {
        if (in_array($paySchet->uslugatovar->IsCustom, [TU::$TOCARD, TU::$TOSCHET])) {
            //при выдаче списание суммы со счета погашения (номинального), комиссии с транзитного счета (выдачи)
            $BalanceIn = new BalancePartner(BalancePartner::IN, $paySchet->IdOrg);
            $BalanceIn->Dec($paySchet->SummPay, 'Платеж ' . $paySchet->ID, 3, $paySchet->ID, 0);
            $BalanceOut = new BalancePartner(BalancePartner::OUT, $paySchet->IdOrg);

            $comis = $paySchet->calcReward();
            if ($comis) {
                $BalanceOut->Dec($comis, 'Комиссия ' . $paySchet->ID, 5, $paySchet->ID, 0);
            }

        } elseif (in_array($paySchet->uslugatovar->IsCustom, [TU::$POGASHECOM, TU::$POGASHATF])) {
            //погашение
            $BalanceIn = new BalancePartner(BalancePartner::IN, $paySchet->IdOrg);
            $BalanceIn->Inc($paySchet->SummPay, 'Платеж ' . $paySchet->ID, 2, $paySchet->ID, 0);

            $comis = $paySchet->calcReward();

            if ($comis && !empty($paySchet->CardNum)) {
                $BalanceOut = new BalancePartner(BalancePartner::OUT, $paySchet->IdOrg);
                $BalanceOut->Dec($comis, 'Комиссия ' . $paySchet->ID, 5, $paySchet->ID, 0);
            }
        }
        return true;
    }

    /**
     * @param PaySchet $paySchet
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function changeBalanceIfNotHaveNominalSchet(PaySchet $paySchet)
    {
        if (in_array($paySchet->uslugatovar->IsCustom, [TU::$TOCARD, TU::$TOSCHET])) {
            //выплата
            $BalanceOut = new BalancePartner(BalancePartner::OUT, $paySchet->IdOrg);
            $BalanceOut->Dec($paySchet->SummPay, 'Платеж ' . $paySchet->ID, 3, $paySchet->ID, 0);

            $comis = $paySchet->calcReward();
            if ($comis) {
                $BalanceOut->Dec($comis, 'Комиссия ' . $paySchet->ID, 5, $paySchet->ID, 0);
            }
        } elseif (in_array($paySchet->uslugatovar->IsCustom, [TU::$VYPLATVOZN, TU::$REVERSCOMIS])) {
            $BalanceOut = new BalancePartner(BalancePartner::OUT, $paySchet->IdOrg);
            $BalanceOut->Dec($paySchet->SummPay, 'Перечисление вознаграждения. Платеж ' . $paySchet->ID, 3, $paySchet->ID, 0);
        } elseif (in_array($paySchet->uslugatovar->IsCustom, [TU::$VYVODPAYS, TU::$PEREVPAYS])) {
            //перевод денег мфо
            $partner = Partner::findOne(['ID' => $paySchet->uslugatovar->ExtReestrIDUsluga]);
            if ($partner && $partner->IsCommonSchetVydacha) {
                //со счета выдачи
                $BalanceOut = new BalancePartner(BalancePartner::OUT, $paySchet->uslugatovar->ExtReestrIDUsluga);
                $BalanceOut->Dec($paySchet->SummPay, 'Списание на перевод средств ' . $paySchet->ID, 1, $paySchet->ID, 0);
            } else {
                //со счета погашения переводится
                $BalanceIn = new BalancePartner(BalancePartner::IN, $paySchet->uslugatovar->ExtReestrIDUsluga);
                $BalanceIn->Dec($paySchet->SummPay, 'Списание на перевод средств ' . $paySchet->ID, 1, $paySchet->ID, 0);

                $comis = $paySchet->calcReward();
                if ($comis) {
                    $BalanceIn->Dec($comis, 'Комиссия ' . $paySchet->ID, 5, $paySchet->ID, 0);
                }
            }
        } else {
            //погашение
            $BalanceIn = new BalancePartner(BalancePartner::IN, $paySchet->IdOrg);
            $BalanceIn->Inc($paySchet->SummPay, 'Платеж ' . $paySchet->ID, 2, $paySchet->ID, 0);
            $comis = $paySchet->calcReward();
            if ($comis) {
                $BalanceIn->Dec($comis, 'Комиссия ' . $paySchet->ID, 5, $paySchet->ID, 0);
            }
        }
        return true;
    }

}
