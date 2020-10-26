<?php


namespace app\models\bank;


class ADGBank implements IBank
{
    public static $bank = 4;

    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        // TODO: Implement confirmPay() method.
    }

    public function transferToCard(array $data)
    {
        // TODO: Implement transferToCard() method.
    }

    public function PayXml(array $params)
    {
        // TODO: Implement PayXml() method.
    }

    public function PayApple(array $params)
    {
        // TODO: Implement PayApple() method.
    }

    public function PayGoogle(array $params)
    {
        // TODO: Implement PayGoogle() method.
    }

    public function PaySamsung(array $params)
    {
        // TODO: Implement PaySamsung() method.
    }

    public function ConfirmXml(array $params)
    {
        // TODO: Implement ConfirmXml() method.
    }

    public function reversOrder($IdPay)
    {
        // TODO: Implement reversOrder() method.
    }
}
