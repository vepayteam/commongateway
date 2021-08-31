<?php

namespace app\commands;

use app\services\exchange_rates\WallettoExchangeRateService;
use Yii;
use yii\console\Controller;

class ExchangeController extends Controller
{
    public function actionUpdate()
    {
        /** @var WallettoExchangeRateService $wallettoExchangeRateService */
        $wallettoExchangeRateService = Yii::$container->get('WallettoExchangeRateService');
        $wallettoExchangeRateService->update();
    }
}
