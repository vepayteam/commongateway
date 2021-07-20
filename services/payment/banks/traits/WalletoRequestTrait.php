<?php

namespace app\services\payment\banks\traits;

use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\walleto\CreatePayRequest;
use app\services\payment\helpers\PaymentHelper;
use Yii;

trait WalletoRequestTrait
{
    /**
     * @param CreatePayForm $createPayForm
     * @return CreatePayRequest
     */
    private function formatCreatePayRequest(CreatePayForm $createPayForm): CreatePayRequest
    {
        $paySchet = $createPayForm->getPaySchet();
        // amount check currency what is provided by default RUB
        $amount = PaymentHelper::convertToFullAmount($paySchet->getSummFull());
        $request = new CreatePayRequest();
        $request->amount = $amount;
        $request->pan = $createPayForm->CardNumber;
        $request->card = [
            'cvv' => $createPayForm->CardCVC,
            'holder' => $createPayForm->CardHolder,
            'expiration_month' => str_pad($createPayForm->CardMonth, 2, '0', STR_PAD_LEFT),
            'expiration_year' => '20' . $createPayForm->CardYear,
        ];
        $request->location['ip'] = $this->getRequestIp();
        //TODO: add address, city, country, login, phone, zip
        $request->client = [
          'email' => $paySchet->UserEmail,
          'address' => $paySchet->AddressUser ?? '',
          'city' => $paySchet->CityUser ?? '',
          'country' => $paySchet->CountryUser ?? '',
          'login' => $paySchet->LoginUser ?? '',
          'phone' => $paySchet->PhoneUser ?? '',
          'zip' => $paySchet->ZipUser ?? '',
        ];
        $request->options = [
            'force3d' => 1,
            'auto_charge' => 1,
            'return_url' => $createPayForm->getReturnUrl(),
        ];
        $request->currency = $paySchet->currency->Code;
        $request->merchant_order_id = $paySchet->ID;
        $request->description = 'Счет №' . $paySchet->ID ?? '';
        return $request;
    }

    /**
     * TODO: move from trait
     * На стороне банка стоит Anti fraud (public ip validation)
     * @return string
     */
    protected function getRequestIp(): string
    {
        if (isset(Yii::$app->params['remote_ip']) && !empty(Yii::$app->params['remote_ip'])) {
            return Yii::$app->params['remote_ip'];
        }

        $ip = Yii::$app->request->remoteIP;

        // если внутренний IP в docker для локальной машины или локалхост
        if (strpos($ip, '172.18.') === 0 || $ip === '127.0.0.1') {
            /* @todo Легаси: выяснить и указать в phpDoc, почему здесь такой IP  */
            $ip = '195.58.60.180';
        }

        return $ip;
    }
}
