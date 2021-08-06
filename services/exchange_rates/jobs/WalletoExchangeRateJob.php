<?php

namespace app\services\exchange_rates\jobs;

use app\services\exchange_rates\WalletoExchangeRateService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class WalletoExchangeRateJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $tryCount;

    public function execute($queue)
    {
        /** @var WalletoExchangeRateService $walletoExchangeRateService */
        $walletoExchangeRateService = Yii::$container->get('WalletoExchangeRateService');
        $walletoExchangeRateService->update($this->tryCount);
    }
}
