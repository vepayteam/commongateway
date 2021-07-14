<?php


namespace app\services\payment\banks\traits;


use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\Check3DSVersionResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2DuplicatedException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\tkb\Authenticate3DSv2Request;
use app\services\payment\forms\tkb\Check3DSVersionRequest;
use app\services\payment\forms\tkb\CreatePay3DS2Request;
use app\services\payment\interfaces\Cache3DSv2Interface;
use app\services\payment\interfaces\Issuer3DSVersionInterface;
use app\services\payment\models\PaySchet;
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

        // обработка ошибок
        $check3DSCacheKey = PaySchet::CHECK_3DS_CACHE_PREFIX . $paySchet->ID;
        if(isset($ans['httperror']['Code'])) {
            // ошибка дубликата
            if(
                $ans['httperror']['Code'] == 'OPERATION_DUPLICATED'
                && Yii::$app->cache->exists($check3DSCacheKey)
            ) {
                try {
                    $ans = json_decode(Yii::$app->cache->get($check3DSCacheKey), true);
                } catch (\Exception $e) {
                    $message = 'Ошибка данных 3ds2check';
                    $paySchet->setError($message);
                    throw new Check3DSv2DuplicatedException($message);
                }
            } elseif ($ans['httperror']['Code'] == 'OPERATION_DUPLICATED') {
                $message = 'Ошибка дублирования запроса 3ds2check';
                $paySchet->setError($message);
                throw new Check3DSv2DuplicatedException($message);
            }
        }

        if(!isset($ans['xml']['CardEnrolled'])) {
            throw new BankAdapterResponseException('Ошибка проверки версии 3ds');
        }

        Yii::$app->cache->set($check3DSCacheKey, json_encode($ans), 600);
        if($ans['xml']['CardEnrolled'] == 'N') {
            $check3DSVersionResponse->version = Issuer3DSVersionInterface::V_1;
        } else {
            $check3DSVersionResponse->version = $ans['xml']['DsInfo']['ProtocolVersion'];
            $check3DSVersionResponse->transactionId = $ans['xml']['ThreeDSServerTransID'];
            $check3DSVersionResponse->cardRefId = $ans['xml']['CardRefId'];

            if(isset($ans['xml']['ThreeDSServerTransID']) && isset($ans['xml']['DsInfo']['ThreeDSMethodURL'])) {
                $check3DSVersionResponse->threeDSServerTransID = $ans['xml']['ThreeDSServerTransID'];
                $check3DSVersionResponse->threeDSMethodURL = $ans['xml']['DsInfo']['ThreeDSMethodURL'];
            }
        }

        Yii::$app->cache->set(
            Cache3DSv2Interface::CACHE_PREFIX_CHECK_DATA . $paySchet->ID,
            $check3DSVersionResponse->getAttributes(),
            60 * 60
        );
        return $check3DSVersionResponse;
    }

    /**
     * @param PaySchet $paySchet
     * @param Check3DSVersionResponse $check3DSVersionResponse
     * @return CreatePayResponse
     * @throws Check3DSv2Exception
     * @throws CreatePayException
     */
    protected function createPay3DSv2(Payschet $paySchet, Check3DSVersionResponse $check3DSVersionResponse)
    {
        return Yii::$app->cache->getOrSet(Cache3DSv2Interface::CACHE_PREFIX_AUTH_RESPONSE . $paySchet->ID, function() use ($paySchet, $check3DSVersionResponse) {
            sleep(5);
            $action = '/api/v1/card/unregistered/debit/3ds2Authenticate';

            $authenticate3DSv2Request = new Authenticate3DSv2Request();
            $authenticate3DSv2Request->ExtId = $paySchet->ID;
            $authenticate3DSv2Request->CardInfo = [
                'CardRefId' => $check3DSVersionResponse->cardRefId,
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

            $payResponse = new CreatePayResponse();
            $payResponse->vesion3DS = $check3DSVersionResponse->version;
            $payResponse->status = BaseResponse::STATUS_DONE;
            $payResponse->cardRefId = $check3DSVersionResponse->cardRefId;

            Yii::$app->cache->set(
                Cache3DSv2Interface::CACHE_PREFIX_CARD_REF_ID . $paySchet->ID,
                $check3DSVersionResponse->cardRefId,
                3600
            );
            Yii::warning('TKBank3DSTrait set cardRefId cache: paySchet.ID=' . $paySchet->ID
                . ' paySchet.Extid=' . $paySchet->Extid
                . ' cardRefId=' . $check3DSVersionResponse->cardRefId
            );
            Yii::warning('TKBank3DSTrait get paySchet cardRefId=' . $paySchet->CardRefId3DS);

            if(array_key_exists('ChallengeData', $ans['xml'])) {
                // если нужна авторизация 3ds через форму
                $payResponse->url = $ans['xml']['ChallengeData']['AcsURL'];
                $payResponse->creq = $ans['xml']['ChallengeData']['Creq'];
            } elseif (array_key_exists('AuthenticationData', $ans['xml'])) {

                // Поддерживается только определенные ECI
                if(
                    $ans['xml']['AuthenticationData']['Status'] == 'NOK' ||
                    !in_array(
                        (int)$ans['xml']['AuthenticationData']['Eci'],
                        Issuer3DSVersionInterface::CURRENT_ECI_ARRAY
                    )
                ) {
                    throw new Check3DSv2Exception('Карта не поддерживается, обратитесь в банк');
                }

                Yii::$app->cache->set(
                    Cache3DSv2Interface::CACHE_PREFIX_AUTH_DATA . $paySchet->ID,
                    json_encode($ans['xml']['AuthenticationData']),
                    3600
                );
                $payResponse->isNeed3DSVerif = false;
                $payResponse->authValue = $ans['xml']['AuthenticationData']['AuthenticationValue'];
                $payResponse->dsTransId = $ans['xml']['AuthenticationData']['DsTransID'];
                $payResponse->eci = $ans['xml']['AuthenticationData']['Eci'];
                $payResponse->threeDSServerTransID = $check3DSVersionResponse->transactionId;
            } else {
                $payResponse->status = BaseResponse::STATUS_ERROR;
            }

            return $payResponse;
        });
    }

}
