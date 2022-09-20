<?php

namespace app\services\payment\banks;

use app\clients\PayloniumClient;
use app\clients\payloniumClient\requests\BalanceRequest;
use app\clients\payloniumClient\requests\GetStatusRequest;
use app\clients\payloniumClient\requests\OutCardPayRequest;
use app\clients\payloniumClient\responses\TransactionStatusResponse;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\data\ClientData;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\PayloniumServerException;
use app\services\payment\exceptions\PayloniumTransactionNotFound;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PartnerBankGate;

class PayloniumAdapter extends BaseAdapter implements IBankAdapter
{
    /**
     * Все запросы шлются на один endpoint в конце нужно добавить username
     * Полный url выглядит так https://api.paylonium.com/api/v1/payment-easy/username
     */
    private const BASE_URL_BANK = 'https://api.paylonium.com/api/v1/payment-easy/';

    /**
     * @deprecated Use {@see bankId()} instead.
     * @var int
     */
    public static $bank = 16;

    /**
     * @var PayloniumClient
     */
    private $api;

    /**
     * {@inheritDoc}
     */
    public static function bankId(): int
    {
        return 16;
    }

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $bankUrl = self::BASE_URL_BANK . $partnerBankGate->Login;
        $privateKeyPath = \Yii::getAlias('@app/config/paylonium/' . $partnerBankGate->AdvParam_1);

        $this->api = new PayloniumClient($bankUrl, $privateKeyPath, $partnerBankGate->PartnerId, $this->getBankId());
    }

    /**
     * @inheritdoc
     */
    public function getBankId(): int
    {
        return self::bankId();
    }

    public function confirm(DonePayForm $donePayForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function createPay(CreatePayForm $createPayForm, ClientData $clientData)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();

        $request = new GetStatusRequest($okPayForm->IdPay);

        try {
            $response = $this->api->checkStatusPay($request);
        } catch (PayloniumServerException $e) {
            \Yii::warning(['PayloniumAdapter checkStatusPay', $e]);

            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = $e->getMessage();

            return $checkStatusPayResponse;
        }

        try {
            $status = $this->getTransactionBaseResponseStatus($response);
        } catch (PayloniumTransactionNotFound $e) {
            \Yii::$app->errorHandler->logException($e);

            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = 'Платеж не найден';

            return $checkStatusPayResponse;
        }

        $checkStatusPayResponse->status = $status;
        switch ($status) {
            case BaseResponse::STATUS_CREATED:
                $checkStatusPayResponse->message = 'Ожидается запрос статуса';
                break;
            case BaseResponse::STATUS_DONE:
                $checkStatusPayResponse->transId = $response->getTrans();
                break;
            case BaseResponse::STATUS_ERROR:
                $checkStatusPayResponse->message = $response->getErrorDescription() ?? BankAdapterResponseException::REQUEST_ERROR_MSG;
                break;
            default:
                break;
        }

        return $checkStatusPayResponse;
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function refundPay(RefundPayForm $refundPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $outCardPayResponse = new OutCardPayResponse();

        /**
         * Для параметра service у paylonium поддерживается 2 значения 24 и 25
         *
         * 24 - вывод на карты
         * 25 - вывод на qiwi кошельки
         *
         * в нашем случае есть лишь вывод на карты
         */
        $request = new OutCardPayRequest(
            $outCardPayForm->paySchet->ID,
            $outCardPayForm->amount,
            24,
            $outCardPayForm->cardnum,
            date('c', $outCardPayForm->paySchet->DateCreate),
            $outCardPayForm->phone
        );

        try {
            $response = $this->api->outCardPay($request);
        } catch (PayloniumServerException $e) {
            \Yii::warning(['PayloniumAdapter outCardPay', $e]);

            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $e->getMessage();

            return $outCardPayResponse;
        }

        try {
            $status = $this->getTransactionBaseResponseStatus($response);
        } catch (PayloniumTransactionNotFound $e) {
            \Yii::$app->errorHandler->logException($e);

            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = 'Платеж не найден';

            return $outCardPayResponse;
        }

        $outCardPayResponse->status = $status;
        switch ($status) {
            case BaseResponse::STATUS_CREATED:
                $outCardPayResponse->message = 'Ожидается запрос статуса';
                break;
            case BaseResponse::STATUS_DONE:
                $outCardPayResponse->trans = $response->getTrans();
                break;
            case BaseResponse::STATUS_ERROR:
                $outCardPayResponse->message = $response->getErrorDescription() ?? BankAdapterResponseException::REQUEST_ERROR_MSG;
                break;
            default:
                break;
        }

        return $outCardPayResponse;
    }

    public function getAftMinSum()
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest): GetBalanceResponse
    {
        $getBalanceResponse = new GetBalanceResponse();
        $getBalanceResponse->bank_name = $getBalanceRequest->bankName;
        $getBalanceResponse->currency = $getBalanceRequest->currency;
        $getBalanceResponse->account_type = $getBalanceRequest->accountType;

        $request = new BalanceRequest();

        try {
            $response = $this->api->getBalance($request);
        } catch (PayloniumServerException $e) {
            \Yii::$app->errorHandler->logException($e);

            throw new BankAdapterResponseException(BankAdapterResponseException::REQUEST_ERROR_MSG);
        }

        $getBalanceResponse->amount = PaymentHelper::convertToFullAmount($response->getAmount());

        return $getBalanceResponse;
    }

    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * Для методов outCardPay и checkStatusPay возвращается один и тот же ответ
     *
     * Результат выполнения операции возвращается по трем параметрам, цитата из документации:
     * "Значения "final", "code", "state" используются для интерпретации результата выполнения платежа.
     * Фактически принимайте решения в вашем алгоритме по следующим сочетаниям значений"
     *
     * В случае если по трем параметрам не было найдено соответствия,
     * то следует считать операцию незаконченной и проверить статус позже
     *
     * Цитата из документации
     * "Если попадутся сочетания значений, которых нет в списке выше, то никак их не интерпретируйте,
     * игнорируйте, считайте, что ответ от сервера не получен."
     *
     *
     * @param TransactionStatusResponse $response
     * @return int
     * @throws PayloniumTransactionNotFound
     */
    private function getTransactionBaseResponseStatus(TransactionStatusResponse $response): int
    {
        if (
            $response->getCode() === 0 &&
            $response->getState() === 60 &&
            $response->getFinal() === 1
        ) {
            return BaseResponse::STATUS_DONE;
        } else if (
            $response->getCode() === 20 &&
            $response->getState() === 80 &&
            $response->getFinal() === 1
        ) {
            return BaseResponse::STATUS_ERROR;
        } else if (
            $response->getCode() === 15 &&
            $response->getState() === -2 &&
            $response->getFinal() === 1
        ) {
            throw new PayloniumTransactionNotFound();
        }

        return BaseResponse::STATUS_CREATED;
    }
}
