<?php

namespace app\services\balance;

use app\models\mfo\MfoBalance;
use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\balance\response\BalanceResponse;
use app\services\balance\traits\BalanceTrait;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\IBankAdapter;
use app\services\payment\exceptions\GateException;
use Yii;
use yii\base\Model;

/**
 * TODO: rename to BalanceService
 * Class Balance - Сервис для получния баланса МФО
 * @package app\services\balance
 */
class Balance extends Model
{
    use BalanceTrait;

    public const BALANCE_CACHE_PREFIX = 'balance_cache_partner_';
    public const BALANCE_CACHE_EXPIRE = 30; // in seconds

    /** @var Partner $partner */
    public $partner;

    public function rules(): array
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
     * @param MfoReq $mfoRequest
     * @return BalanceResponse
     */
    public function getAllBanksBalance(MfoReq $mfoRequest): BalanceResponse
    {
        return Yii::$app->cache->getOrSet(self::BALANCE_CACHE_PREFIX . $mfoRequest->mfo, function () use ($mfoRequest) {
            return $this->build($mfoRequest);
        }, self::BALANCE_CACHE_EXPIRE);
    }

    /**
     * @param MfoReq $mfoRequest
     * @return BalanceResponse
     * @throws GateException
     */
    public function build(MfoReq $mfoRequest): BalanceResponse
    {
        $mfoBalanceRepository = new MfoBalance($this->partner);
        $partnerId = $mfoRequest->mfo;
        // Получаем все активные шлюзы
        $enabledBankGates = $mfoBalanceRepository->getAllEnabledPartnerBankGatesId($partnerId);
        if (!$enabledBankGates) {
            return $this->balanceError(BalanceResponse::BALANCE_UNAVAILABLE_ERROR_MSG);
        }
        $balanceList = [];
        $this->uslugaTovar = $mfoBalanceRepository->getPartnersUslugatovarById($partnerId); //TODO: refactor remove
        foreach ($enabledBankGates as $activeGate) {
            $bank = $mfoBalanceRepository->getBankById($activeGate->BankId); // Current gate bank
            $bankAdapter = $this->buildAdapter($bank);
            $getBalanceRequest = $this->formatRequest($bank);
            try {
                $getBalanceResponse = $bankAdapter->getBalance($getBalanceRequest);
            } catch (\Exception $exception) {
                Yii::error('Balance service: ' . $exception->getMessage() . ' - PartnerId: ' . $partnerId);
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
     * @return IBankAdapter
     * @throws GateException
     */
    protected function buildAdapter($bank): IBankAdapter
    {
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($this->partner, $this->uslugaTovar, $bank);
        return $bankAdapterBuilder->getBankAdapter();
    }
}
