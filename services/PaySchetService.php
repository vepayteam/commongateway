<?php

namespace app\services;

use app\models\kfapi\KfCard;
use app\models\kfapi\KfOut;
use app\models\kfapi\KfPay;
use app\models\payonline\Provparams;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\models\PaySchet;
use yii\base\Component;

/**
 * @todo Легаси. Зарефакторить логику.
 */
class PaySchetService extends Component
{

    private const MAX_ERROR_INFO_MESSAGE_LENGHT = 250;

    /**
     * Создание платежа по Provparams.
     *
     * @param Provparams $provparams
     * @param int $agent
     * @param int $typeWidget
     * @param int $bank
     * @param int $idOrg
     * @param string $extid
     * @param int $autoPayIdGate
     * @return array|null
     * @throws CreatePayException
     * @todo Легаси. Переименовать/упразднить. Возвращаемое значение должно быть объектом.
     */
    public function createPay(Provparams $provparams, $agent = 0, $typeWidget = 0, $bank = 0, $idOrg = 0, $extid = '', $autoPayIdGate = 0): ?array
    {
        $ret = null;

        $paySchet = $this->addPayschet(null, $provparams, $agent, 0, $typeWidget, $bank, $idOrg, $extid, $autoPayIdGate, 1800);
        if ($paySchet) {
            $ret = ['IdPay' => $paySchet->ID];
        }

        return $ret;
    }

    /**
     * "Сохранение для платежа данных"
     *
     * @param User|null $user
     * @param Provparams $provparams
     * @param int $agent
     * @param int $idCardActivate
     * @param int $typeWidget [0 - web/communal 1 - mobile 2 - merchant 3 - mfo]
     * @param int $bank
     * @param int $idOrg
     * @param string $extid
     * @param int $autoPayIdGate
     * @param int $timeout
     * @param string $dogovor
     * @param string $fio
     * @param int $smsNeed
     * @return PaySchet
     * @throws CreatePayException
     */
    protected function addPayschet(
        ?User $user,
        Provparams $provparams,
        $agent = 0,
        $idCardActivate = 0,
        $typeWidget = 0,
        $bank = 0,
        $idOrg = 0,
        $extid = '',
        $autoPayIdGate = 0,
        $timeout = 86400, // !!! изменен default переменной $timeout, старое значение 900
        $dogovor = '',
        $fio = '',
        $smsNeed = 0,
        $regcard = 0
    ): PaySchet
    {
        $paySchet = new PaySchet();

        $paySchet->IdUsluga = $provparams->Usluga->ID;
        $paySchet->IdQrProv = (int)$provparams->Usluga->ProfitIdProvider;
        $paySchet->QrParams = implode("|", $provparams->param);
        $paySchet->IdUser = $user->ID ?? 0;
        $paySchet->SummPay = $provparams->summ;
        $paySchet->UserClickPay = 0;
        $paySchet->DateCreate = time();
        $paySchet->IdKard = $idCardActivate;
        $paySchet->Status = 0;
        $paySchet->DateOplat = 0;
        $paySchet->DateLastUpdate = time();
        $paySchet->PayType = 0;
        $paySchet->TimeElapsed = $timeout;
        $paySchet->ExtKeyAcces = 0;
        $paySchet->CountSendOK = 0;
        $paySchet->Period = 0;

        $paySchet->Schetcheks = '';
        $paySchet->IdAgent = $agent;
        $paySchet->IsAutoPay = $autoPayIdGate > 0 ? 1 : 0;
        $paySchet->AutoPayIdGate = $autoPayIdGate;
        $paySchet->TypeWidget = $typeWidget;
        $paySchet->Bank = $bank;
        $paySchet->IdOrg = $idOrg;
        $paySchet->Extid = $extid;
        $paySchet->sms_accept = ($smsNeed === 1 ? 0 : 1);
        $paySchet->Dogovor = $dogovor;
        $paySchet->FIO = $fio;
        $paySchet->regcard = $regcard;

        if (!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        \Yii::warning("addPayschet IdKard={$idCardActivate} Bank={$bank}, IdOrg={$idOrg} Extid={$extid}", 'payschet');

        return $paySchet;
    }

    /**
     * "Проверка на повтор счета по внешнему ID".
     *
     * @param string $extid
     * @param $uslugaId
     * @param $orgId
     * @return array|null
     * @todo Легаси. Переименовать/упразднить. Возвращаемое значение должно быть объектом.
     */
    public function getPaySchetExt($extid, $uslugaId, $orgId): ?array
    {
        $result = null;

        /** @var PaySchet|null $paySchet */
        $paySchet = PaySchet::find()
            ->andWhere([
                'Extid' => $extid,
                'IdUsluga' => $uslugaId,
                'IdOrg' => $orgId,
            ])
            ->andWhere(['>', 'DateCreate', time() - 86400 * 100])
            ->one();

        if ($paySchet !== null) {
            $result = [
                'IdPay' => $paySchet->ID,
                'sumin' => $paySchet->SummPay / 100.0,
                'summ' => $paySchet->SummPay + $paySchet->ComissSumm,
                'url' => $paySchet->UrlFormPay,
                'params' => $paySchet->QrParams,
            ];
        }

        return $result;
    }

    /**
     * Погашение займа.
     *
     * @param $user
     * @param array $params
     * @param KfPay $kfPay
     * @param $usl
     * @param $bank
     * @param $idOrg
     * @param $autoPayIdGate
     * @return array
     * @throws CreatePayException
     */
    public function payToMfo($user, $params, KfPay $kfPay, $usl, $bank, $idOrg, $autoPayIdGate): array
    {
        $provparams = new Provparams();
        $provparams->prov = $usl;
        $provparams->param = $params;
        $provparams->summ = round((float)($kfPay->amount) * 100.0);
        $provparams->Usluga = Uslugatovar::findOne(['ID' => $provparams->prov]);

        $paySchet = $this->addPayschet(
            $user,
            $provparams,
            0,
            0,
            3,
            $bank,
            $idOrg,
            $kfPay->extid,
            $autoPayIdGate,
            $kfPay->timeout * 60,
            $kfPay->document_id,
            $kfPay->fullname,
            0,
            $kfPay->regcard
        );

        if (!empty($KfPay->successurl)) {
            $this->setReturnUrl($paySchet, $KfPay->successurl);
        } elseif (!empty($provparams->Usluga->UrlReturn)) {
            $this->setReturnUrl($paySchet, $provparams->Usluga->UrlReturn);
        }

        if (!empty($kfPay->failurl)) {
            $this->setReturnUrlFail($paySchet, $kfPay->failurl);
        } elseif (!empty($provparams->Usluga->UrlReturnFail)) {
            $this->setReturnUrlFail($paySchet, $provparams->Usluga->UrlReturnFail);
        }

        if (!empty($kfPay->cancelurl)) {
            $this->setReturnUrlCancel($paySchet, $kfPay->cancelurl);
        } elseif (!empty($provparams->Usluga->UrlReturnCancel)) {
            $this->setReturnUrlCancel($paySchet, $provparams->Usluga->UrlReturnCancel);
        }

        if (!empty($kfPay->postbackurl)) {
            $this->setReturnUrlPostback($paySchet, $kfPay->postbackurl);
        }

        if (!empty($kfPay->postbackurl_v2)) {
            $this->setReturnUrlPostbackV2($paySchet, $kfPay->postbackurl_v2);
        }

        return ['IdPay' => $paySchet->ID, 'summ' => $paySchet->getSummFull(), 'TimeElapsed' => round($kfPay->timeout), 'checkurl' => $provparams->Usluga->UrlCheckReq];
    }

    /**
     * Вычада займа на карту.
     *
     * @param User|null $user
     * @param array $params
     * @param KfOut $kfOut
     * @param int $usl
     * @param int $bank
     * @param int $idOrg
     * @param int $needSms
     * @return array
     * @throws CreatePayException
     * @todo Легаси. Оптимизировать логику. Метод должен возвращать объект.
     */
    public function payToCard(?User $user, $params, KfOut $kfOut, $usl, $bank, $idOrg, $needSms = 0): array
    {
        $provparams = new Provparams();
        $provparams->prov = $usl;
        $provparams->param = $params;
        $provparams->summ = round(floatval($kfOut->amount) * 100.0);
        $provparams->Usluga = Uslugatovar::findOne(['ID' => $provparams->prov]);

        $paySchet = $this->addPayschet(
            $user,
            $provparams,
            0,
            0,
            3,
            $bank,
            $idOrg,
            $kfOut->extid,
            0,
            900,
            $kfOut->document_id,
            $kfOut->fullname,
            $needSms
        );

        return ['IdPay' => $paySchet->ID, 'summ' => $paySchet->getSummFull()];
    }


    /**
     * Создание платежа.
     *
     * @param int $idCardActivate
     * @param KfCard $kfCard
     * @param int $TypwWidget
     * @param int $bank
     * @param int $org
     * @return array|null [IdPay, Sum]
     * @throws CreatePayException
     * @todo Легаси...
     */
    public function payActivateCard($user, $idCardActivate, $kfCard, $TypwWidget, $bank = 0, $org = 0)
    {
        $ret = null;

        try {
            $sum = random_int(100, 1000) / 100.0;
        } catch (\Exception $e) {
            $sum = 100;
        }

        /**
         * @todo Легаси. Сделать по-нормальному инициализацию $provparams.
         */
        $provparams = new Provparams();
        $provparams->load([
            'Provparams' => [
                'prov' => 1,
                'param' => [0 => $sum],
                'summ' => $sum
            ]
        ]);
        $provparams->summ = round(floatval($provparams->summ) * 100.0);
        $provparams->Usluga = Uslugatovar::findOne(['ID' => $provparams->prov]);

        if ($provparams->Usluga) {
            $paySchet = $this->addPayschet($user, $provparams, 0, $idCardActivate, $TypwWidget, $bank, $org, $kfCard->extid, 0, $kfCard->timeout * 60);
            $idpay = $paySchet->ID;
            if (!empty($kfCard->successurl)) {
                $this->setReturnUrl($paySchet, $kfCard->successurl);
            }
            if (!empty($kfCard->failurl)) {
                $this->setReturnUrlFail($paySchet, $kfCard->failurl);
            }
            if (!empty($kfCard->cancelurl)) {
                $this->setReturnUrlFail($paySchet, $kfCard->cancelurl);
            }
            if (!empty($kfCard->postbackurl)) {
                $this->setReturnUrlPostback($paySchet, $kfCard->postbackurl);
            }
            if (!empty($kfCard->postbackurl_v2)) {
                $this->setReturnUrlPostbackV2($paySchet, $kfCard->postbackurl_v2);
            }

            $ret = ['IdPay' => $idpay, 'Sum' => $sum];
        }
        return $ret;
    }

    /**
     * "Не прошел запрос в банк - отменить сразу счет"
     *
     * @param $idPay
     * @param string $message
     * @todo Легаси. Принимать объект вместо ID. Переименовать.
     */
    public function cancelReq($idPay, $message = 'Ошибка запроса')
    {
        $paySchet = PaySchet::find()
            ->andWhere(['ID' => $idPay])
            ->andWhere(['Status' => 0])
            ->one();

        if ($paySchet !== null) {
            $paySchet->Status = 2;
            $paySchet->ErrorInfo = mb_substr($message, 0, self::MAX_ERROR_INFO_MESSAGE_LENGHT);
            $paySchet->save(false);
        }
    }

    /**
     * @param PaySchet $paySchet
     * @param $url
     * @todo Легаси. Удалить.
     */
    private function setReturnUrl(PaySchet $paySchet, $url)
    {
        $paySchet->updateAttributes(['SuccessUrl' => $url]);
    }

    /**
     * @param PaySchet $paySchet
     * @param $url
     * @todo Легаси. Удалить.
     */
    private function setReturnUrlFail(PaySchet $paySchet, $url)
    {
        $paySchet->updateAttributes(['FailedUrl' => $url]);
    }

    /**
     * @param PaySchet $paySchet
     * @param $url
     * @todo Легаси. Удалить.
     */
    private function setReturnUrlCancel(PaySchet $paySchet, $url)
    {
        $paySchet->updateAttributes(['CancelUrl' => $url]);
    }

    /**
     * @param PaySchet $paySchet
     * @param $url
     * @todo Легаси. Удалить.
     */
    private function setReturnUrlPostback(PaySchet $paySchet, $url)
    {
        $paySchet->updateAttributes(['PostbackUrl' => $url]);
    }

    /**
     * @param PaySchet $paySchet
     * @param $url
     * @todo Легаси. Удалить.
     */
    private function setReturnUrlPostbackV2(PaySchet $paySchet, $url)
    {
        $paySchet->updateAttributes(['PostbackUrl_v2' => $url]);
    }

    /**
     * Обновление карты в счете.
     *
     * @param int $idPay
     * @param int $idKard
     * @todo Легаси. Удалить.
     */
    public function setKardToPaySchet($idPay, $idKard)
    {
        if ($idKard) {
            $paySchet = PaySchet::findOne($idPay);
            if ($paySchet !== null) {
                $paySchet->updateAttributes(['IdKard' => $idKard]);
            }
        }
    }

    /**
     * @param $idOrder
     * @param $data
     * @todo Легаси. Удалить.
     */
    public function setIdOrder($idOrder, $data)
    {
        $paySchet = PaySchet::findOne($data['IdPay']);
        if ($paySchet !== null) {
            $paySchet->updateAttributes(['IdOrder' => $idOrder]);
        }
    }

}