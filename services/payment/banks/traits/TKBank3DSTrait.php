<?php


namespace app\services\payment\banks\traits;


use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\Check3DSVersionResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\tkb\Authenticate3DSv2Request;
use app\services\payment\forms\tkb\Check3DSVersionRequest;
use app\services\payment\forms\tkb\CreatePay3DS2Request;
use app\services\payment\interfaces\Cache3DSv2Interface;
use app\services\payment\interfaces\Issuer3DSVersionInterface;
use Yii;
use yii\helpers\Json;

trait TKBank3DSTrait
{
    /**
     * @param CreatePayForm $createPayForm
     * @return Check3DSVersionResponse
     * @throws BankAdapterResponseException
     */
    protected function check3DSVersion(CreatePayForm $createPayForm)
    {
        $action = '/api/v1/card/unregistered/debit/3ds2check/storecard';

        $paySchet = $createPayForm->getPaySchet();
        $check3DSVerisonRequest = new Check3DSVersionRequest();
        $check3DSVerisonRequest->ExtId = $paySchet->ID;
        $check3DSVerisonRequest->Amount = $paySchet->getSummFull();
        $check3DSVerisonRequest->CardInfo = [
            'CardNumber' => $createPayForm->CardNumber,
            'CardHolder' => $createPayForm->CardHolder,
            'ExpirationYear' => '20' . $createPayForm->CardYear,
            'ExpirationMonth' => str_pad($createPayForm->CardMonth, 2, '0', STR_PAD_LEFT),
            'CVV' => $createPayForm->CardCVC,
        ];

        $queryData = Json::encode($check3DSVerisonRequest->getAttributes());

        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        $check3DSVersionResponse = new Check3DSVersionResponse();

        if(!isset($ans['xml']['CardEnrolled'])) {
            throw new BankAdapterResponseException('Ошибка проверки версии 3ds');
        }

        if($ans['xml']['CardEnrolled'] == 'N') {
            $check3DSVersionResponse->version = Issuer3DSVersionInterface::V_1;
        } else {

            if(!in_array($ans['xml']['DsInfo']['ProtocolVersion'], Issuer3DSVersionInterface::V_2)) {
                throw new BankAdapterResponseException('Ошибка проверки версии 3ds');
            }
            $check3DSVersionResponse->version = $ans['xml']['DsInfo']['ProtocolVersion'];
            $check3DSVersionResponse->transactionId = $ans['xml']['ThreeDSServerTransID'];
            $check3DSVersionResponse->url = $ans['xml']['DsInfo']['ThreeDSMethodURL'];
            $check3DSVersionResponse->cardRefId = $ans['xml']['CardRefId'];
        }

        return $check3DSVersionResponse;
    }

    /**
     * @param CreatePayForm $createPayForm
     * @param Check3DSVersionResponse $check3DSVersionResponse
     * @return CreatePayResponse
     * @throws CreatePayException
     */
    protected function createPay3DSv2(CreatePayForm $createPayForm, Check3DSVersionResponse $check3DSVersionResponse)
    {
        $action = '/api/v1/card/unregistered/debit/3ds2Authenticate';

        $paySchet = $createPayForm->getPaySchet();
        $authenticate3DSv2Request = new Authenticate3DSv2Request();
        $authenticate3DSv2Request->ExtId = $paySchet->ID;
        $authenticate3DSv2Request->CardInfo = [
            'CardNumber' => $createPayForm->CardNumber,
            'ExpirationYear' => "20" . $createPayForm->CardYear,
            'ExpirationMonth' => $createPayForm->CardMonth,
        ];
        $authenticate3DSv2Request->Amount = $paySchet->getSummFull();

        // TODO:
        $headers = Yii::$app->request->headers;
        $authenticate3DSv2Request->AuthenticateInfo = [
            'BrowserInfo' => [
                'IP' => Yii::$app->request->remoteIP,
                'AcceptHeader' => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
                'JavaEnabled' =>  true,
                'Language' => 'ru',
                'ColorDepth' => 24,
                'ScreenHeight' => 1080,
                'ScreenWidth' => 1920,
                'TZ' => -180,
                'UserAgent' => $headers->has('User-Agent') ? $headers['User-Agent'] :  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36",
            ],
            "ChallengeWindowSize" => "05",
            "ThreeDSCompInd" => "U",
            "NotificationURL" => $paySchet->getOrderdoneUrl(),
            "ThreeDSServerTransID" => $check3DSVersionResponse->transactionId,
        ];

        $queryData = Json::encode($authenticate3DSv2Request->getAttributes());

        // TODO: response as object
        $ans = $this->curlXmlReq($queryData, $this->bankUrl . $action);

        if(!isset($ans['xml'])) {
            throw new CreatePayException('Ошибка аутентификации клиента');
        }

        if($ans['xml']['AuthenticationData']['Eci'] == '1') {
            throw new Check3DSv2Exception('');
        }

        $payResponse = new CreatePayResponse();
        $payResponse->vesion3DS = $check3DSVersionResponse->version;
        $payResponse->status = BaseResponse::STATUS_DONE;
        $payResponse->cardRefId = $check3DSVersionResponse->cardRefId;

        if(array_key_exists('ChallengeData', $ans['xml'])) {
            // если нужна авторизация 3ds через форму
            $payResponse->url = $ans['xml']['ChallengeData']['AcsUrl'];
            $payResponse->creq = $ans['xml']['ChallengeData']['Creq'];
        } elseif (array_key_exists('AuthenticationData', $ans['xml'])) {
            Yii::$app->cache->set(
                Cache3DSv2Interface::CACHE_PREFIX_AUTH_DATA . $paySchet->ID,
                json_encode($ans['xml']['AuthenticationData']),
                3600
            );
            $payResponse->isNeed3DSVerif = false;
            $payResponse->authValue = $ans['xml']['AuthenticationData']['AuthenticationValue'];
            $payResponse->dsTransId = $ans['xml']['AuthenticationData']['DsTransID'];
            $payResponse->eci = $ans['xml']['AuthenticationData']['Eci'];
        } else {
            $payResponse->status = BaseResponse::STATUS_ERROR;
        }

        return $payResponse;
    }

}
