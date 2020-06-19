<?php


namespace app\models\bank;


interface IBank
{
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
