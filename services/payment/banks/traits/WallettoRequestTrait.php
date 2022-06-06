<?php

namespace app\services\payment\banks\traits;

use app\services\payment\banks\data\ClientData;
use app\services\payment\banks\WallettoBankAdapter;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\walletto\CreatePayRequest;
use app\services\payment\helpers\PaymentHelper;
use Yii;

/**
 * @todo Remove useless trait. Move code to {@see WallettoBankAdapter}.
 * @deprecated
 */
trait WallettoRequestTrait
{
    private $publicIpAddress = '84.38.187.23';

    /**
     * @param CreatePayForm $createPayForm
     * @param ClientData $clientData
     * @return CreatePayRequest
     */
    private function formatCreatePayRequest(
        CreatePayForm $createPayForm,
        ClientData $clientData
    ): CreatePayRequest
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
        // По ответам walletto нужно передавать в location['ip'] публичный адрес, иначе придет ошибка
        // Если находимся в девмоде или в тестмоде, то указываем наш публичный ip адрес
        // В остальных случаях указываем ip адрес клиента
        $request->location['ip'] = (Yii::$app->params['DEVMODE'] === 'Y' || Yii::$app->params['TESTMODE'] === 'Y')
            ? $this->publicIpAddress
            : $paySchet->IPAddressUser;
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
            'expiration_timeout' => (int)($paySchet->TimeElapsed / 60),
        ];
        $request->currency = $paySchet->currency->Code;
        $request->merchant_order_id = $paySchet->ID;
        $request->description = 'Счет №' . $paySchet->ID ?? '';

        $request->secure3d = [
            'browser_details' => [
                'browser_ip' => $clientData->ip,
                'browser_user_agent' => $clientData->headerUserAgent,
                'browser_accept_header' => $clientData->headerAccept === '/' ? 'text/html' : $clientData->headerAccept,
                'browser_screen_height' => $clientData->browserScreenHeight ?? '',
                'browser_screen_width' => $clientData->browserScreenWidth ?? '',
                'browser_timezone' => $clientData->browserTimezoneOffset ?? '',
                'window_height' => $clientData->browserWindowHeight ?? '',
                'window_width' => $clientData->browserWindowWidth ?? '',
                'browser_language' => $clientData->browserLanguage ?? 'ru',
                'browser_color_depth' => $clientData->browserColorDepth ?? '',
                'browser_java_enabled' => $clientData->browserJavaEnabled ?? '',
            ],
        ];

        return $request;
    }
}