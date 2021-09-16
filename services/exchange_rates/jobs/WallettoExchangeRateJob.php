<?php

namespace app\services\exchange_rates\jobs;

use app\services\exchange_rates\WallettoExchangeRateService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class WallettoExchangeRateJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $tryCount;

    public function execute($queue)
    {
        /** @var WallettoExchangeRateService $wallettoExchangeRateService */
        $wallettoExchangeRateService = Yii::$container->get('WallettoExchangeRateService');
        $wallettoExchangeRateService->update($this->tryCount);
    }
}
