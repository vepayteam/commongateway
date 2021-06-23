<?php

namespace app\Api\Payment\Cauri;

use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\forms\cauri\CheckStatusPayRequest;
use app\services\payment\forms\cauri\CreatePayRequest;
use app\services\payment\forms\cauri\RecurrentPayRequest;
use app\services\payment\forms\cauri\RefundPayRequest;
use app\services\payment\models\PartnerBankGate;
use Exception;
use Vepay\Cauri\Client\Request\UserResolveRequest;
use Vepay\Cauri\Resource\Balance;
use Vepay\Cauri\Resource\Card;
use Vepay\Cauri\Resource\Payin;
use Vepay\Cauri\Resource\Refund;
use Vepay\Cauri\Resource\Transaction;
use Vepay\Cauri\Resource\User;
use Vepay\Gateway\Client\Response\ResponseInterface;
use Yii;
use yii\helpers\Json;

/**
 * TODO: move Facade to Cauri module
 * Class CauriApiFacade
 * @package app\Api\Payment\Cauri
 */
class CauriApiFacade
{
    /**
     * @var array $requestOptions
     */
    private $requestOptions;

    /**
     * CauriApiFacade constructor.
     * @param PartnerBankGate $gate
     */
    public function __construct(PartnerBankGate $gate)
    {
        $this->requestOptions = [
            'public_key' => $gate->Login,
            'private_key' => $gate->Token,
        ];
    }

    /**
     * Docs: https://docs.pa.cauri.com/api/#resolve-a-user
     * @param UserResolveRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function resolveUser(UserResolveRequest $request): ResponseInterface
    {
        $user = new User();
        Yii::warning('CauriAPI resolveUser req:' . Json::encode($request->getParameters()));
        return $user->__call('resolve', [
            $request->getParameters(), $this->requestOptions
        ]);
    }

    /**
     * Docs: https://docs.pa.cauri.com/api/#charge-a-card
     * @param CreatePayRequest $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function payInCreate(CreatePayRequest $request): ResponseInterface
    {
        $payIn = new Payin();
        Yii::warning('CauriAPI payInCreate req:' . Json::encode($request->getAttributes()));
        return $payIn->__call('create', [
            $request->getAttributes(), $this->requestOptions
        ]);
    }

    /**
     * Docs: https://docs.pa.cauri.com/api/#get-transaction-by-id
     * @param CheckStatusPayRequest $request
     * @return ResponseInterface
     */
    public function getTransactionStatus(CheckStatusPayRequest $request): ResponseInterface
    {
        $transaction = new Transaction();
        Yii::warning('CauriAPI getTransactionStatus req:' . Json::encode($request->getAttributes()));
        return $transaction->__call('status', [
            $request->getAttributes(), $this->requestOptions
        ]);
    }

    /**
     * Docs: https://docs.pa.cauri.com/api/#reverse-a-payment
     * @param RefundPayRequest $request
     * @return ResponseInterface
     */
    public function refundCreate(RefundPayRequest $request): ResponseInterface
    {
        $refund = new Refund();
        Yii::warning('CauriAPI refundCreate req:' . Json::encode($request->getAttributes()));
        return $refund->__call('create', [
            $request->getAttributes(), $this->requestOptions
        ]);
    }

    /**
     * Docs: https://docs.pa.cauri.com/api/#manual-recurring
     * @param RecurrentPayRequest $request
     * @return ResponseInterface
     */
    public function cardManualRecurring(RecurrentPayRequest $request): ResponseInterface
    {
        $card = new Card();
        Yii::warning('CauriAPI manualRecurring req:' . Json::encode($request->getAttributes()));
        return $card->__call('manualRecurring', [
            $request->getAttributes(), $this->requestOptions
        ]);
    }

    /**
     * Docs: https://docs.pa.cauri.com/api/#get-merchants-balance
     * @param array $request
     * @return ResponseInterface
     */
    public function getBalance(array $request): ResponseInterface
    {
        $balance = new Balance();
        return $balance->__call('getBalance', [
            $request, $this->requestOptions
        ]);
    }
}
