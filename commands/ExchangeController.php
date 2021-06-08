<?php

namespace app\commands;

use app\services\exchange_rates\WalletoExchangeRateService;
use Yii;
use yii\console\Controller;

class ExchangeController extends Controller
{
    public function actionUpdate()
    {
        /** @var WalletoExchangeRateService $walletoExchangeRateService */
        $walletoExchangeRateService = Yii::$container->get('WalletoExchangeRateService');
        $walletoExchangeRateService->update();
    }
}
