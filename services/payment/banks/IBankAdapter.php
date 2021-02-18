<?php


namespace app\services\payment\banks;


use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

// TODO: удалить лишние методы
interface IBankAdapter
{
    /**
     * @param PartnerBankGate $partnerBankGate
     * @return mixed
     */
    public function setGate(PartnerBankGate $partnerBankGate);

    /**
     * @return int
     */
    public function getBankId();

    /**
     * TODO: rename to confirmPay
     * @param DonePayForm $donePayForm
     * @return ConfirmPayResponse
     */
    public function confirm(DonePayForm $donePayForm);

    /**
     * Завершение оплаты (запрос статуса)
     *
     * @param string $idpay
     * @param int $org
     * @param bool $isCron
     * @return array [status (1 - оплачен, 2,3 - не оплачен, 0 - в процессе), message, IdPay (id pay_schet), Params]
     * @throws \yii\db\Exception
     */
    public function confirmPay($idpay, $org = 0, $isCron = false);

    /**
     * перевод средств на карту
     * @param array $data
     * @return array|mixed
     */
    public function transferToCard(array $data);

    /**
     * @param PaySchet $paySchet
     * @param CreatePayForm $createPayForm
     * @throws BankAdapterResponseException
     * @throws Check3DSv2Exception
     * @throws CreatePayException
     * @return CreatePayResponse
     */
    public function createPay(CreatePayForm $createPayForm);

    /**
     * Оплата без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function PayXml(array $params);

    /**
     * Оплата без формы ApplePay
     * @param array $params
     * @return array
     */
    public function PayApple(array $params);

    /**
     * Оплата без формы GooglePay
     * @param array $params
     * @return array
     */
    public function PayGoogle(array $params);

    /**
     * Оплата без формы SamsungPay
     * @param array $params
     * @return array
     */
    public function PaySamsung(array $params);

    /**
     * Финиш оплаты без формы (PCI DSS)
     * @param array $params
     * @return array
     */
    public function ConfirmXml(array $params);

    /**
     * Возврат оплаты
     * @param int $IdPay
     * @return array
     * @throws \yii\db\Exception
     */
    public function reversOrder($IdPay);

    /**
     * @param CheckStatusPayForm $checkStatusPayForm
     * @return CheckStatusPayResponse
     */
    public function checkStatusPay(OkPayForm $okPayForm);

    /**
     * @param AutoPayForm $autoPayForm
     * @return CreateRecurrentPayResponse
     * @throws GateException
     */
    public function recurrentPay(AutoPayForm $autoPayForm);

    /**
     * @param RefundPayForm $refundPayForm
     * @return RefundPayResponse
     * @throws RefundPayException
     */
    public function refundPay(RefundPayForm $refundPayForm);

    /**
     * @param OutCardPayForm $outCardPayForm
     * @return OutCardPayResponse
     * @return mixed
     */
    public function outCardPay(OutCardPayForm $outCardPayForm);

}
