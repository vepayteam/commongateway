<?php


namespace app\services\payment\banks;


use app\models\payonline\Uslugatovar;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

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
     * @return mixed
     */
    public function pay(CreatePayForm $createPayForm);

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

}
