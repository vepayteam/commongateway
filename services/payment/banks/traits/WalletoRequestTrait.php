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
        $amount = PaymentHelper::convertToRub($paySchet->getSummFull());
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
        $request->client['email'] = $createPayForm->Email;
        $request->options['force3d'] = true;
        $request->currency = 'RUB'; //todo: get from request
        $request->merchant_order_id = $paySchet->ID;
        $request->description = 'Счет №' . $paySchet->ID ?? '';
        return $request;
    }

    /**
     * TODO: move from adapter
     * На стороне банка стоит Anti fraud (public ip validation)
     * @return string
     */
    protected function getRequestIp(): string
    {
        if (isset(Yii::$app->params['remote_ip']) ?? !empty(Yii::$app->params['remote_ip'])) {
            return Yii::$app->params['remote_ip'];
        }
        return Yii::$app->request->remoteIP;
    }
}
