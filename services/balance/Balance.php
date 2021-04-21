<?php

namespace app\services\balance;

use app\models\mfo\MfoBalance;
use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\balance\response\BalanceResponse;
use app\services\balance\traits\BalanceTrait;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\IBankAdapter;
use Yii;
use yii\base\Model;

/**
 * TODO: rename to BalanceService
 * Class Balance
 * @package app\services\balance
 */
class Balance extends Model
{
    use BalanceTrait;

    public const BALANCE_CACHE_PREFIX = 'balance_cache_partner_';
    public const BALANCE_CACHE_EXPIRE = 30; // in seconds

    /** @var Partner $partner */
    public $partner;

    public function rules()
    {
        return [
            [['partner'], 'required']
        ];
    }
    /** @var Uslugatovar $uslugaTovar */
    private $uslugaTovar;
    /** @var MfoBalance $mfoBalanceRepository */
    private $mfoBalanceRepository;

    /**
     * @param \app\models\mfo\MfoReq $mfoRequest
     * @return \app\services\balance\response\BalanceResponse
     */
    public function getAllBanksBalance(MfoReq $mfoRequest): BalanceResponse
    {
        return Yii::$app->cache->getOrSet(self::BALANCE_CACHE_PREFIX . $mfoRequest->mfo, function () use ($mfoRequest) {
            return $this->build($mfoRequest);
        }, self::BALANCE_CACHE_EXPIRE);
    }

    /**
     * @param \app\models\mfo\MfoReq $mfoRequest
     * @return \app\services\balance\response\BalanceResponse
     * @throws \app\services\payment\exceptions\GateException
     */
    public function build(MfoReq $mfoRequest): BalanceResponse
    {
        $mfoBalanceRepository = new MfoBalance($this->partner);
        $partnerId = $mfoRequest->mfo;
        // get partners all enabled gates
        $enabledBankGates = $mfoBalanceRepository->getAllEnabledPartnerBankGatesId($partnerId);
        if (!$enabledBankGates) {
            return $this->balanceError(BalanceResponse::BALANCE_UNAVAILABLE_ERROR_MSG);
        }
        $balanceList = [];
        $this->uslugaTovar = $mfoBalanceRepository->getPartnersUslugatovarById($partnerId);
        foreach ($enabledBankGates as $activeGate) {
            $bank = $mfoBalanceRepository->getBankById($activeGate->BankId); // Current gate bank
            $bankAdapter = $this->buildAdapter($bank);
            $getBalanceRequest = new GetBalanceRequest();
            $getBalanceRequest->currency = 'RUB'; //todo add dynamic currency requests
            try {
                $getBalanceResponse = $bankAdapter->getBalance($getBalanceRequest);
            } catch (\Exception $exception) {
                Yii::error('Service balance: ' . $exception->getMessage() . ' - PartnerId: ' . $partnerId);
                continue;
            }
            $bankBalanceResponse = array_filter((array)$getBalanceResponse);
            if (isset($bankBalanceResponse) && !empty($bankBalanceResponse)) {
                $bankBalanceResponse['bank_name'] = $bank->getName();
                $balanceList[] = $bankBalanceResponse;
            }
        }
        if (!$balanceList) {
            return $this->balanceError(BalanceResponse::BALANCE_UNAVAILABLE_ERROR_MSG);
        }
        $balanceResponse = new BalanceResponse();
        $balanceResponse->status = BalanceResponse::STATUS_DONE;
        $balanceResponse->balance = $balanceList;
        return $balanceResponse;
    }

    /**
     * @param $bank
     * @return \app\services\payment\banks\IBankAdapter
     * @throws \app\services\payment\exceptions\GateException
     */
    protected function buildAdapter($bank): IBankAdapter
    {
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($this->partner, $this->uslugaTovar, $bank);
        return $bankAdapterBuilder->getBankAdapter();
    }
}
