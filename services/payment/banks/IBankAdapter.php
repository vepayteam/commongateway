<?php

namespace app\services\payment\banks;


use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\IdentInitResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CheckStatusPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\GetBalanceForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Vepay\Gateway\Client\Validator\ValidationException;

// TODO: удалить лишние методы
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
     * TODO: rename to confirmPay
     * @param DonePayForm $donePayForm
     * @return ConfirmPayResponse
     */
    public function confirm(DonePayForm $donePayForm);

    /**
     * @param PaySchet $paySchet
     * @param CreatePayForm $createPayForm
     * @throws BankAdapterResponseException
     * @throws Check3DSv2Exception
     * @throws CreatePayException
     * @throws MerchantRequestAlreadyExistsException
     * @return CreatePayResponse
     */
    public function createPay(CreatePayForm $createPayForm);

    /**
     * @param CheckStatusPayForm $checkStatusPayForm
     * @return CheckStatusPayResponse
     */
    public function checkStatusPay(OkPayForm $okPayForm);

    /**
     * @param AutoPayForm $autoPayForm
     * @return CreateRecurrentPayResponse
     * @throws GateException
     */
    public function recurrentPay(AutoPayForm $autoPayForm);

    /**
     * @param RefundPayForm $refundPayForm
     * @return RefundPayResponse
     * @throws RefundPayException
     */
    public function refundPay(RefundPayForm $refundPayForm);

    /**
     * @param OutCardPayForm $outCardPayForm
     * @throws ValidationException
     * @return OutCardPayResponse
     * @return mixed
     */
    public function outCardPay(OutCardPayForm $outCardPayForm);

    /**
     * @return int
     */
    public function getAftMinSum();

    /**
     * @param GetBalanceRequest $getBalanceRequest
     * @return GetBalanceResponse
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest);


    /**
     * @param OutPayAccountForm $outPayaccForm
     * @return TransferToAccountResponse
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm);

    /**
     * @param Ident $ident
     * @return IdentInitResponse
     */
    public function identInit(Ident $ident);

    /**
     * @param Ident $ident
     * @return IdentGetStatusResponse
     */
    public function identGetStatus(Ident $ident);

    public function currencyExchangeRates();

    public function sendP2p(SendP2pForm $sendP2pForm);
}
