<?php

namespace app\services\balance;

use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\services\balance\response\BalanceResponse;
use app\services\balance\traits\BalanceTrait;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\IBankAdapter;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\repositories\BankRepository;
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
    public const BALANCE_CACHE_EXPIRE = 10; // in seconds

    /** @var Partner $partner */
    public $partner;
    /** @var BalanceResponse */
    private $response;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->response = new BalanceResponse();
    }

    public function rules(): array
    {
        return [
            [['partner'], 'required']
        ];
    }

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
        // Получаем все активные шлюзы
        $enabledBankGates =  $this->getActiveBankGates();
        if (!$enabledBankGates) {
            $this->response->setError(BalanceResponse::BALANCE_UNAVAILABLE_ERROR_MSG);
            return $this->response;
        }
        $bankResponse = [];
        foreach ($enabledBankGates as $activeGate) {
            $bank = BankRepository::getBankById($activeGate->BankId);
            $bankAdapter = $this->buildAdapter($bank);
            $getBalanceRequest = $this->formatRequest($activeGate, $bank);
            try {
                /** @var GetBalanceResponse */
                $getBalanceResponse = $bankAdapter->getBalance($getBalanceRequest);
            } catch (\Exception $exception) {
                Yii::warning('Balance service: ' . $exception->getMessage() . ' - PartnerId: ' . $mfoRequest->mfo);
                continue;
            }
            if (isset($getBalanceResponse) && !empty($getBalanceResponse->amount)) {
                $bankResponse[] = $getBalanceResponse;
            }
        }
        if (!$bankResponse) {
            $this->response->setError(BalanceResponse::BALANCE_UNAVAILABLE_ERROR_MSG);
            return $this->response;
        }
        $this->response->setBankBalance($bankResponse);
        return $this->response;
    }

    /**
     * @param $bank
     * @return IBankAdapter
     * @throws GateException
     */
    protected function buildAdapter($bank): IBankAdapter
    {
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBankId($this->partner, $bank);
        return $bankAdapterBuilder->getBankAdapter();
    }
}
